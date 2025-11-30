<?php
include "../../header.php";
session_start();
if (!isset($_SESSION['adminData'])) {
    include 'eror.php';
    exit;
}
 ?>

<div class="container-fluid">
    <div class="row">
        <div class="text-center text-danger fs-5">

            <div class="fw-bold text-danger h1">Welcome back IT Programmer!</div>

        </div>
    </div>
</div>

<div class="form-group">
    <div class="bg-danger text-white overflow-hidden position-relative">
        <div class="container position-relative">
            <div class="d-inline-block fw-bold me-2">🗞️ Latest News:</div>
            <div class="news-ticker d-inline-block text-nowrap ">
                <span>Breaking: IT Department releases new policy update — HR to
                    conduct evaluation next week — Employee portal maintenance scheduled for
                    October 14, 2025 — Stay tuned for system upgrade details!
                    Don’t forget that the logout is the icon.
                </span>
            </div>
        </div>
    </div>

    <style>
        .news-ticker span {
            display: inline-block;
            animation: ticker 25s linear infinite;
            font-size: 2rem;

        }

        @keyframes ticker {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(-100%);
            }
        }
    </style>

    <!-- Registration Form -->
    <form id="staff_registration" method="post" class="p-4 m-3 mx-auto border rounded shadow-sm bg-light" style="max-width: 400px; margin: auto;">
        <div class=" text-center">
            <h5 class="mb-3 text-center">Staff Registration</h5>
            <div id="logout" class="fa fa-door-open cursor-pointer h1 mx-auto"></div>
        </div>

        <!-- User Role -->
        <div class="mb-3">
            <label for="user_type" class="form-label">User Role</label>
            <select class="form-select" name="user_role" id="user_type" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="HRSM">Human Resources</option>
                <option value="EMPLOYEE">Employee</option>
            </select>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
        </div>

        <!-- Submit Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-danger">Submit</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#staff_registration').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                type: "POST",
                url: base_url + "/authentication/action.php?action=admin_staff_register", // ✅ fixed spacing
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json", // ✅ ensures automatic JSON parsing
                beforeSend: function() {
                    $('#staff_registration button[type="submit"]').prop('disabled', true).text('Submitting...');
                },
                success: function(response) {
                    if (response.status === 1) {
                        // ✅ Bootstrap alert for better UX
                        $('body').append(`
                        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index:9999;">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                        $('#staff_registration')[0].reset();
                    } else {
                        $('body').append(`
                        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index:9999;">
                            ${response.message || 'Registration failed.'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    }
                },
                error: function(xhr, status, error) {
                    $('body').append(`
                    <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index:9999;">
                        An error occurred: ${error}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                },
                complete: function() {
                    $('#staff_registration button[type="submit"]').prop('disabled', false).text('Submit');
                }
            });
        });
    });
</script>