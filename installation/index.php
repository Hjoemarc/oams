<?php include '../header.php'; ?>
<main id="main" class="installer-page d-flex flex-column justify-content-center align-items-center mt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card">
                    <form id="install-form">
                        <div class="mb-1">
                            <div class="card-header text-center">
                                <h2><?php echo "Installation "; ?></h2>
                                
                            </div>
                            <?php ?>
                            <div class="custom-file mx-auto">
                                <h6 class="text-center">System Logo</h6>
                                <img src="../assets/svgs/solid/file.svg" class="img rounded preview mx-auto d-block"
                                    alt="" id="systemLogoPreview"  style="cursor:pointer;width:150px;height:150px;object-fit:contain;">
                                <input type="file" class="custom-file-input d-none" accept="image/*" id="systemLogo"
                                    style="display:none;">
                            </div>
                            <script>
                                document.getElementById('systemLogoPreview').onclick = function () {
                                    document.getElementById('systemLogo').click();
                                };
                                document.getElementById('systemLogo').onchange = function (event) {
                                    const [file] = event.target.files;
                                    if (file) {
                                        document.getElementById('systemLogoPreview').src = URL.createObjectURL(file);
                                    }
                                };
                            </script>
                            <h5 class="text-center">System Information</h5>
                            <div class="input-group mb-2 row justify-content-center">
                                <div class="col-sm-6 col-md-4">
                                    <input type="text" class="form-control" name="system_title"
                                        placeholder="System Title" required>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <input type="text" class="form-control" name="system_description"
                                        placeholder="System Description" required>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-center">Admin Information</h5>
                        <div class="mb-2 row justify-content-center">
                            <div class="col-sm-6 col-md-4">
                                <input type="text" class="form-control" name="firstname" placeholder="First Name"
                                    required>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <input type="text" class="form-control" name="middlename"
                                    placeholder="Middle Name (Optional)">
                            </div>
                        </div>
                        <div class="mb-2 row justify-content-center">
                            <div class="col-sm-6 col-md-4">
                                <input type="text" class="form-control" name="lastname" placeholder="Last Name"
                                    required>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <input type="email" class="form-control" name="email" placeholder="Email" required>
                            </div>
                        </div>
                        <div class="mb-3 row justify-content-center">
                            <div class="col-sm-6 col-md-4">
                                <input type="text" class="form-control" name="username" placeholder="Username" required>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <input type="password" class="form-control" name="password" placeholder="Password"
                                    required>
                            </div>
                        </div>
                        <div class="mb-1 text-center">
                            <button type="submit" class="btn btn-secondary">INSTALL</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../footer.php' ?>

