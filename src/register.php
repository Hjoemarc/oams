<?php include '../header.php'; ?>
<style>
@media(max-width:768px) {
    .container {
        margin: 0 !important;
    }

    .card-header {
        width: 90vw;
    }

    .card-body {
        display: flex !important;
        justify-content: start !important;
        align-items: start !important;
        height: 75vh !important;
        padding: .3rem !important;
    }

    .card-body::-webkit-scrollbar {
        display: none !important;
    }

    .fontRegister {
        font-size: 2rem !important;
    }
}

.verification-section {
    display: none;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    border-left: 4px solid #007bff;
}

.password-feedback {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
<main id="main" class="login-page">
    <div class="container row justify-content-center align-items-center ">
        <div class="row justify-content-center align-items-center col-md-12 col-12">
            <div class="col-md-9 col-12 p-0 m-0">
                <div class="card-header shadow">
                    <div class="card-header bg-gradient-primary text-white shadow  text-center">
                        <h4 class="mb-0 p-2 fw-bold text-white">Sign-up to Online Accreditation System</h4>
                    </div>
                    <div class="card-body scroll">
                        <!-- Registration Form -->
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
                            <input type="hidden" name="is_active" value="Pending">
                            <input type="hidden" name="user_role" value="faculty">
                            <div class="col-md-4">
                                <label class="form-label">Program <span class="text-danger">*</span></label>
                                <select required class="form-select" name="program_id" id="Program">
                                    <option value="">Select Program</option>
                                    <?php 
                                            $stmt = $pdo->prepare("SELECT * FROM programs");
                                            $stmt->execute();
                                            $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach($programs as $prog) :
                                        ?>
                                    <option value="<?= htmlspecialchars($prog["program_id"]) ?>">
                                        <?= htmlspecialchars($prog["program_name"] . '(' . $prog["program_code"] . ')') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <select required class="form-select" name="department_id" id="Department">
                                    <option value="">Select Department</option>
                                    <?php 
                                            $stmt = $pdo->prepare("SELECT * FROM departments");
                                            $stmt->execute();
                                            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach($departments as $dept) :
                                        ?>
                                    <option value="<?= htmlspecialchars($dept["department_id"]) ?>">
                                        <?= htmlspecialchars($dept["department_name"] . '(' . $dept["department_code"] . ')') ?>
                                    </option>
                                    <?php endforeach; ?>
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
                                <div class="mt-2">
                                    <span>Already have an account? </span><a href="index.php"
                                        class="text-decoration-none">Sign In</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<script>
document.addEventListener('DOMContentLoaded', function() {
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
<?php include '../footer.php'; ?>