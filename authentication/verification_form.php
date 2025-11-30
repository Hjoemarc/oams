<?php
include '../header.php';
session_start();

if (!isset($_SESSION['email_queue'])) {
    include 'eror.php';
    exit;
}
?>
<style>
    .verification-code {
        height: 50px;
        font-size: 30px;
        letter-spacing: 5px;
        outline: none;
    }
</style>
<!-- Verification Form -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header text-center shadow bg-primary">
                    <h4 class="text-center">Verifiying code</h4>
                    <p class="card-text"><?php echo get_option('system_description'); ?></p>
                </div>
                <div class="card-body">
                    <div id="verification-feedback" class="text-center"></div>
                    <form id="verification_form" class="text-center">
                        <div class="col-md-12">
                            <label class="form-label">Email Verification</label>
                            <input type="text" class="form-control no-decoration text-lg text-center verification-code"
                                id="verification_code" name="verification" maxlength="6" required>
                        </div>
                    </form>




                    <div class="col-12 text-center mt-3">
                        <div class="mt-2">
                            <span> Go to </span><a href="#" id="signinback" class="text-decoration-none">Sign In</a>
                        </div>
                    </div>
                    <!-- Form End -->

                </div>
            </div>
        </div>
    </div>
</div>