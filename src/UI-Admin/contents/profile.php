<section>
    <?php $employees = $getEmployee["user_id"]; ?>
    <div class="d-flex justify-content-between align-items-center mb-0 col-md-12 col-12 flex-wrap">
        <div class="mx-2 col-md-3 col-12">
            <h4 class=""><i class="fa-regular fa-circle-user me-1"></i>User Profile</h4>
            <small class="text-muted ">Manage User Profile</small>
        </div>
    </div>
    <div class="row">
        <div class="column p-2 m-0 rounded-2 col-12 col-md-4">
            <div class="card rounded-2">
                <div class="d-flex flex-column w-100 align-items-center justify-content-center p-2">
                    <div class="w-100 d-flex justify-content-start ps-3 pt-1">
                        <a href="index.php?page=contents/users" class="btn btn-danger btn-sm"><i
                                class="fa-solid fa-arrow-left me-1"></i> Back</a>
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
        <!-- PERSONAL INFORMATIONS TAB -->
        <div class="column p-2 m-0 rounded-2 col-12 col-md-8 height tab-pane fade show active" role="tabpanel"
            id="Personal">
            <form id="profile_update">
                <div class="card rounded-2 profile-contents" style="overflow-y: scroll;">
                    <input type="hidden" name="user_id" value="<?= $getEmployee["user_id"] ?>">
                    <!-- PERSONAL INFORMATION HEADER -->
                    <div class="col-md-12 d-flex">
                        <div class="header ps-3 pt-3 col-md-5">
                            <h5 class="m-0 p-0">
                                <i class="fa-solid fa-circle-info me-2"></i>Personal Informations
                            </h5>
                        </div>
                        <?php if($getEmployee["is_active"] == 'Active'){ ?>
                        <div class="col-md-7 d-flex justify-content-end me-5">
                            <button type="submit" class="btn btn-sm btn-danger px-5 mt-3 me-5">Update</button>
                        </div>
                        <?php } else {} ?>
                    </div>

                    <!-- PERSONAL INFORMATION CONTENTS -->
                    <div class="row flex-wrap col-md-12 col-12 p-3">
                        <div class="col-md-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="firstname" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["first_name"] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middlename" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["middl_ename"] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastname" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["last_name"] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Suffix</label>
                            <select class="form-select" name="suffix">
                                <option value="" <?= empty($getEmployee["suffix"] ?? '') ? 'selected' : '' ?>>Select
                                    suffix (optional)</option>
                                <option value="Jr" <?= ($getEmployee["suffix"] ?? '') == 'Jr' ? 'selected' : '' ?>>Jr
                                </option>
                                <option value="Sr" <?= ($getEmployee["suffix"] ?? '') == 'Sr' ? 'selected' : '' ?>>Sr
                                </option>
                                <option value="II" <?= ($getEmployee["suffix"] ?? '') == 'II' ? 'selected' : '' ?>>II
                                </option>
                                <option value="III" <?= ($getEmployee["suffix"] ?? '') == 'III' ? 'selected' : '' ?>>III
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Citizenship</label>
                            <input type="text" name="citizenship" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["citizenship"] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" id="gender" class="form-select">
                                <option value="">Select Gender</option>
                                <option value="MALE" <?= ($getEmployee["gender"] ?? '') == 'MALE' ? 'selected' : '' ?>>
                                    Male</option>
                                <option value="FEMALE"
                                    <?= ($getEmployee["gender"] ?? '') == 'FEMALE' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Civil Status</label>
                            <select name="civil_status" id="civil_status" class="form-select">
                                <option value="">Select Civil Status</option>
                                <option value="Single"
                                    <?= ($getEmployee["civil_status"] ?? '') == 'Single' ? 'selected' : '' ?>>Single
                                </option>
                                <option value="Married"
                                    <?= ($getEmployee["civil_status"] ?? '') == 'Married' ? 'selected' : '' ?>>Married
                                </option>
                                <option value="Widowed"
                                    <?= ($getEmployee["civil_status"] ?? '') == 'Widowed' ? 'selected' : '' ?>>Widowed
                                </option>
                                <option value="Separated"
                                    <?= ($getEmployee["civil_status"] ?? '') == 'Separated' ? 'selected' : '' ?>>
                                    Separated</option>
                                <option value="Divorced"
                                    <?= ($getEmployee["civil_status"] ?? '') == 'Divorced' ? 'selected' : '' ?>>Divorced
                                </option>
                                <option value="Annulled"
                                    <?= ($getEmployee["civil_status"] ?? '') == 'Annulled' ? 'selected' : '' ?>>Annulled
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Religion</label>
                            <select name="religion" id="religion" class="form-select">
                                <option value="">Select Religion</option>
                                <option value="Roman Catholic"
                                    <?= ($getEmployee["religion"] ?? '') == 'Roman Catholic' ? 'selected' : '' ?>>Roman
                                    Catholic</option>
                                <option value="Islam"
                                    <?= ($getEmployee["religion"] ?? '') == 'Islam' ? 'selected' : '' ?>>Islam</option>
                                <option value="Iglesia ni Cristo"
                                    <?= ($getEmployee["religion"] ?? '') == 'Iglesia ni Cristo' ? 'selected' : '' ?>>
                                    Iglesia ni Cristo</option>
                                <option value="Protestant"
                                    <?= ($getEmployee["religion"] ?? '') == 'Protestant' ? 'selected' : '' ?>>Protestant
                                </option>
                                <option value="Born Again Christian"
                                    <?= ($getEmployee["religion"] ?? '') == 'Born Again Christian' ? 'selected' : '' ?>>
                                    Born Again Christian</option>
                                <option value="Seventh-day Adventist"
                                    <?= ($getEmployee["religion"] ?? '') == 'Seventh-day Adventist' ? 'selected' : '' ?>>
                                    Seventh-day Adventist</option>
                                <option value="Buddhist"
                                    <?= ($getEmployee["religion"] ?? '') == 'Buddhist' ? 'selected' : '' ?>>Buddhist
                                </option>
                                <option value="Jehovah's Witness"
                                    <?= ($getEmployee["religion"] ?? '') == 'Jehovah\'s Witness' ? 'selected' : '' ?>>
                                    Jehovah's Witness</option>
                                <option value="Mormon"
                                    <?= ($getEmployee["religion"] ?? '') == 'Mormon' ? 'selected' : '' ?>>Mormon
                                </option>
                                <option value="Aglipayan"
                                    <?= ($getEmployee["religion"] ?? '') == 'Aglipayan' ? 'selected' : '' ?>>Aglipayan
                                </option>
                                <option value="None"
                                    <?= ($getEmployee["religion"] ?? '') == 'None' ? 'selected' : '' ?>>None</option>
                                <option value="Others"
                                    <?= ($getEmployee["religion"] ?? '') == 'Others' ? 'selected' : '' ?>>Others
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birthday</label>
                            <?php
                                $birthday = $getEmployee["birthday"] ?? '';
                                if (!empty($birthday)) {
                                    // If it's already in YYYY-MM-DD format, use it directly
                                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
                                        $formattedBirthday = $birthday;
                                    } else {
                                        // Try to convert from other formats
                                        $formattedBirthday = date('Y-m-d', strtotime($birthday));
                                    }
                                } else {
                                    $formattedBirthday = '';
                                }
                                ?>
                            <input type="date" name="birthday" id="birthday" class="form-control"
                                value="<?= htmlspecialchars($formattedBirthday) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Age</label>
                            <input type="text" readonly name="age" id="age" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["age"] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birth Place</label>
                            <input type="text" name="birthPlace" id="birthPlace" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["birthPlace"] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact" id="contact" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["contact"] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["email"] ?? '') ?>">
                        </div>
                    </div>
                    <!-- ADDRESS INFORMATION HEADER -->
                    <div class="header ps-3 pt-3">
                        <h5 class="m-0 p-0">
                            <i class="fa-solid fa-circle-info me-2"></i>Address Informations
                        </h5>
                    </div>
                    <!-- ADDRESS INFORMATION CONTENTS -->
                    <div class="row flex-wrap col-md-12 col-12 p-3 h-auto pb-5">
                        <div class="col-md-3">
                            <label class="form-label">House/Block No.</label>
                            <input type="text" name="houseBlock" id="houseBlock" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["houseBlock"] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Street</label>
                            <input type="text" name="street" id="street" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["street"] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subdivision</label>
                            <input type="text" name="subdivision" id="subdivision" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["subdivision"] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" name="barangay" id="barangay" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["barangay"] ?? '') ?>">
                        </div>
                        <div class="col-md-3" style="z-index: 2 !important;">
                            <label class="form-label">City/Municipality</label>
                            <input type="text" name="city_muntinlupa" id="city_muntinlupa" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["city_muntinlupa"] ?? '') ?>">
                        </div>
                        <div class="col-md-3" style="z-index: 2 !important;">
                            <label class="form-label">Province</label>
                            <input type="text" name="province" id="province" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["province"] ?? '') ?>">
                        </div>
                        <div class="col-md-3" style="z-index: 2 !important;">
                            <label class="form-label">Zip Code</label>
                            <input type="text" name="zip_code" id="zip_code" class="form-control"
                                value="<?= htmlspecialchars($getEmployee["zip_code"] ?? '') ?>">
                        </div>
                        <?php if($getEmployee["is_active"] == 'Active'){ ?>
                        <div class="col-md-12 d-flex justify-content-end pb-5">
                            <button type="submit" class="btn btn-sm btn-danger px-5 mt-3 me-2">Update</button>
                        </div>
                        <?php } else {} ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileContents = document.querySelector('.profile-contents');
    if (profileContents) {
        // Override the scrollbar display property
        profileContents.style.setProperty('scrollbar-width', 'thin', 'important');
        profileContents.style.setProperty('-webkit-scrollbar', 'auto', 'important');
        
        // Add custom scrollbar styling
        const style = document.createElement('style');
        style.textContent = `
            .profile-contents::-webkit-scrollbar {
                display: block !important;
                width: 8px !important;
            }
            .profile-contents::-webkit-scrollbar-track {
                background: #f1f1f1 !important;
                border-radius: 4px !important;
            }
            .profile-contents::-webkit-scrollbar-thumb {
                background: #c1c1c1 !important;
                border-radius: 4px !important;
            }
            .profile-contents::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8 !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    const birthdayInput = document.getElementById('birthday');
    const ageInput = document.getElementById('age');

    // Function to calculate age from birthday
    function calculateAge(birthday) {
        if (!birthday) return '';

        const birthDate = new Date(birthday);
        const today = new Date();

        // Check if birthdate is valid and not in the future
        if (birthDate > today) {
            ageInput.classList.add('is-invalid');
            return 'Invalid date';
        }

        ageInput.classList.remove('is-invalid');

        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        const dayDiff = today.getDate() - birthDate.getDate();

        // Adjust age if birthday hasn't occurred this year yet
        if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
            age--;
        }

        return age >= 0 ? age : 0;
    }

    // Function to validate birthday (not in future)
    function validateBirthday(birthday) {
        if (!birthday) return true;

        const birthDate = new Date(birthday);
        const today = new Date();

        if (birthDate > today) {
            birthdayInput.classList.add('is-invalid');
            return false;
        }

        birthdayInput.classList.remove('is-invalid');
        return true;
    }

    // Calculate age when birthday changes
    birthdayInput.addEventListener('change', function() {
        if (validateBirthday(this.value)) {
            const age = calculateAge(this.value);
            ageInput.value = age;
        } else {
            ageInput.value = '';
        }
    });

    // Real-time age calculation
    birthdayInput.addEventListener('input', function() {
        if (validateBirthday(this.value)) {
            const age = calculateAge(this.value);
            ageInput.value = age;
        } else {
            ageInput.value = '';
        }
    });

    // Calculate initial age if birthday is already set
    if (birthdayInput.value) {
        if (validateBirthday(birthdayInput.value)) {
            const initialAge = calculateAge(birthdayInput.value);
            ageInput.value = initialAge;
        }
    }

    // Make age field read-only since it's auto-calculated
    ageInput.readOnly = true;
    ageInput.placeholder = 'Auto-calculated from birthday';
});
</script>