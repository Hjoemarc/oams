<?php
session_start();
if (!isset($_SESSION['adminData'])) {
  include 'eror.php';
  exit;
}

$admin_id = $_SESSION['adminData']['user_id'];
?>

<div class="bg-gradient-primary justify-content-between d-flex">
    <div class="mx-3 d-flex align-items-center justify-content-center ">
        <img src="../../assets/image/system_logo/pueri-logo.png" class="image-header me-2">
        <h4 class="text-white m-0 ">Online Accreditation Management System for Zamboanga Peninsula Polytechnic State University </h4>
    </div>
    <div class="justify-content-between p-4 mx-2 d-flex">
        <div class="notification me-5">
        </div>
        <div class="">
            <i class="fas fa-user-shield"></i>
            <span><?php echo get_employee('first_name',$admin_id) . " " . get_employee('last_name',$admin_id); ?></span>
        </div>

        <button type="button" id="logoutBtn">
            <i class="fas fa-sign-out-alt text-black ms-1"></i>
        </button>
        <div class="logoutDomain col-md-3 col-8 h-auto shadow rounded-1 flex-column border" id="logoutDomain"
            style="display:none; background-color: #fff !important;">
            <div class="header-logout bg-danger p-3 d-flex align-items-start justify-content-start w-100 rounded-top">
                <strong class="text-white">Logout Confirmation</strong>
            </div>
            <div class="body-logout py-4 px-4">
                <span class="fw-bold text-muted"> Are you sure you want to logout?</span>
            </div>
            <div class="footer-logout w-100 d-flex align-items-end justify-content-end gap-3 pb-3 pe-3 pt-2"
                style="border-top: solid .5px #0e0e0e4f !important;">
                <button class="m-0 btn btn-danger" id="logout" class="logout" style="cursor: pointer;">
                    yes
                </button>
                <button class="m-0 btn btn-dark" type="button">Cancel</button>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    $('html').css('scroll-behavior', 'smooth');
});
 document.addEventListener('DOMContentLoaded', function() {
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutDomain = document.getElementById('logoutDomain');
        const cancelBtn = logoutDomain.querySelector('.btn-dark');
        const confirmLogoutBtn = document.getElementById('logout');
        
        // Show/hide logout confirmation popup
        logoutBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            positionLogoutPopup();
            
            if (logoutDomain.style.display === 'none' || !logoutDomain.style.display) {
                logoutDomain.style.opacity = '0';
                logoutDomain.style.display = 'flex';
                logoutDomain.style.transform = 'translateY(-10px)';
                
                setTimeout(() => {
                    logoutDomain.style.opacity = '1';
                    logoutDomain.style.transform = 'translateY(0)';
                }, 10);
            } else {
                logoutDomain.style.opacity = '0';
                logoutDomain.style.transform = 'translateY(-10px)';
                
                setTimeout(() => {
                    logoutDomain.style.display = 'none';
                }, 5000);
            }
        });
        
        // Position the popup relative to the logout button
        function positionLogoutPopup() {
            const btnRect = logoutBtn.getBoundingClientRect();
            const domainWidth = logoutDomain.offsetWidth;
            
            logoutDomain.style.position = 'fixed';
            
            if (btnRect.left + domainWidth > window.innerWidth) {
                logoutDomain.style.left = `${btnRect.left - domainWidth + btnRect.width}px`;
            } else {
                logoutDomain.style.left = `${btnRect.left}px`;
            }
            
            logoutDomain.style.top = `${btnRect.bottom + 5}px`;
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (logoutDomain.style.display !== 'none') {
                positionLogoutPopup();
            }
        });
        
        // Cancel button closes the popup
        cancelBtn.addEventListener('click', closeLogoutPopup);
        
        // Logout button handler - using AJAX
        confirmLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const $this = $(this);
            
            $this.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out...');
            $this.prop('disabled', true);
            
            $.ajax({
                url: base_url + "authentication/action.php?action=logout",
                method: "POST",
                dataType: "json",
                success: function(response) {
                    if (response.status == 1) {
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            toast: true,
                            position: "top-end",
                            timer: 3000,
                            showConfirmButton: false,
                        }).then(() => {
                            window.location.href = base_url + (response.redirect_url || "index.php");
                        });
                    } else {
                        showError(response.message);
                        $this.text("Yes");
                        $this.prop('disabled', false);
                    }
                },
                error: function() {
                    console.error("AJAX error");
                    $this.text("Yes");
                    $this.prop('disabled', false);
                }
            });
        });
        
        // Close popup function
        function closeLogoutPopup() {
            logoutDomain.style.opacity = '0';
            logoutDomain.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                logoutDomain.style.display = 'none';
            }, 200);
        }
        
        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!logoutDomain.contains(e.target) && e.target !== logoutBtn) {
                closeLogoutPopup();
            }
        });
        
        // Close when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && logoutDomain.style.display !== 'none') {
                closeLogoutPopup();
            }
        });
    });
</script>