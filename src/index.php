<?php include '../header.php';
initInstaller()
    ?>

<div class="header col-md-12 col-12 d-flex justify-content-center align-items-center py-2">
    <div class="bg-gradient-primary rounded-2 col-md-11 col-11 py-2 px-3">
        <h4 class="card-title text-white m-0">
            <img src="../assets/image/system_logo/zppsu-logo.png" class="me-1 ">
            Online Accreditation Management System for Zamboanga Peninsula Polytechnic State University 
        </h4>
    </div>
</div>

<main id="main" class="login-page">
    <div class="container ">
        <div class="row">
            <div class="col-md-4 col-11 mx-auto">
                <div class="shadow">
                    <div class="card-header text-white bg-gradient-primary text-center shadow bg-primary">
                        <h4 class="card-title fontHeader fw-light">Login Access</h4>
                        <p class="card-text">Web-based system</p>
                    </div>
                    <div class="card-body bg-white shadow ">
                        <form id="login-form" class="form-floating" method="post">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="username"
                                    placeholder="Username ">
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password: ">
                            </div>
                            <div class="mb-3 text-center">
                                <button type="submit" class="btn bg-gradient-primary mb-0 col-md-12 col-12"><i
                                        class="bi bi-person-plus-fill me-1 w-100"></i>Login</button>
                            </div>
                            <div class="w-100 text-center m-0">
                                <a href="forget_acc.php" class="m-0">Forget Account?</a>
                            </div>
                            <div class="col-12 text-center mt-1">
                                <div class="">
                                    <span>Don't have an account? </span><a href="register.php"
                                        class="text-decoration-none">Sign Up</a>
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
        
        // Create show password toggle for password field
        const passwordToggle = document.createElement('button');
        passwordToggle.type = 'button';
        passwordToggle.innerHTML = 'Show';
        passwordToggle.style.position = 'absolute';
        passwordToggle.style.right = '10px';
        passwordToggle.style.top = '50%';
        passwordToggle.style.transform = 'translateY(-10px)';
        passwordToggle.style.background = 'none';
        passwordToggle.style.border = 'none';
        passwordToggle.style.cursor = 'pointer';
        passwordToggle.style.fontSize = '12px';
        
        // Add toggle buttons to password fields
        passwordInput.parentNode.style.position = 'relative';
        passwordInput.parentNode.appendChild(passwordToggle);
        
        
        // Toggle password visibility
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            passwordToggle.innerHTML = type === 'password' ? 'Show' : 'Hide';
        });
    });
</script>
<?php include '../footer.php' ?>