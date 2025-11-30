<?php include '../header.php';?>

<main id="main" class="login-page">
    <div class="container ">
        <div class="row">
            <div class="col-md-4 col-11 mx-auto">
                <div class="shadow">
                    <div class="card-header text-white bg-gradient-primary text-center shadow bg-primary">
                        <h4 class="card-title fontHeader fw-light">Forget Password</h4>
                    </div>
                    <div class="card-body bg-white shadow ">
                        <form id="forget-account-form" class="form-floating" method="post">
                            <div class="mb-3">
                                <label class="form-label">Username or Email</label>
                                <input type="text" class="form-control" name="username" placeholder="input username or email account">
                            </div>
                            <div class="mb-3 text-center">
                                <button type="submit" class="btn btn-primary mb-2 col-md-12 col-12"><i
                                        class="bi bi-person-plus-fill me-1 w-100"></i>submit</button>
                            </div>
                        </form>
                        <div class="w-100 text-center">
                                <a href="index.php">Go back to Login</a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../footer.php' ?>