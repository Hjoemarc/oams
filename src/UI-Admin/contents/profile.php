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
            <form id="profile-update">
                <div class="card rounded-2 profile-contents" style="overflow-y: scroll;">
                    <input type="hidden" name="user_id" value="<?= $getEmployee['user_id'] ?>">

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