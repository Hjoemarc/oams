
<nav id="sidebar" class="navbarHide">
    <div style="width: 240px; border-radius: 5px;">
        <div class="sidebar-list m-2">
            <a href="index.php?page=home" class="nav-item nav-home rounded-2">
                <span class=""><i class=""></i></span> Dashboard
            </a>
            
            <!-- Human Resources Section -->
            <button class="toggle-btn collapsed rounded-2" data-target="hr-section">
                Human Resources <i class="toggle-icon"><i class="fa-solid fa-caret-down"></i></i>
            </button>
            <div id="hr-section" class="toggle-section">
                <a href="index.php?page=contents/recruitment" class="nav-item nav-recruitment my-1 rounded-2">
                    <span class=""><i class=""></i></span> Employees
                </a>
                <a href="index.php?page=contents/leave" class="nav-item nav-leave my-1 rounded-2">
                    <span class=""><i class=""></i></span> Leave
                </a>
                <a href="index.php?page=contents/dept_job" class="nav-item nav-dept_job my-1 rounded-2">
                    <span class=""><i class=""></i></span> Departments & Jobs
                </a>
                <a href="index.php?page=contents/201" class="nav-item nav-201 my-1 rounded-2">
                    <span class=""><i class=""></i></span> 201 Files
                </a>
                <a href="index.php?page=contents/hr_settings" class="nav-item nav-hr_settings my-1 rounded-2">
                    <span class=""><i class=""></i></span> HR settings
                </a>
                <!-- <a href="index.php?page=contents/reports" class="nav-item nav-reports my-1 rounded-2">
                    <span class=""><i class=""></i></span> Reports
                </a> -->
            </div>
            
            <!-- Payroll Section -->
            <button class="toggle-btn collapsed rounded-2" data-target="payroll-section">
                Payroll <i class="toggle-icon"><i class="fa-solid fa-caret-down"></i></i>
            </button>
            <div id="payroll-section" class="toggle-section">
                <a href="index.php?page=contents/payroll" class="nav-item nav-payroll my-1 rounded-2">
                    <span class=""><i class=""></i></span> payroll
                </a>
                <a href="index.php?page=contents/payslip" class="nav-item nav-payslip my-1 rounded-2">
                    <span class=""><i class=""></i></span> Payslip
                </a>
                <a href="index.php?page=contents/attendance" class="nav-item nav-attendance my-1 rounded-2">
                    <span class=""><i class=""></i></span> Attendance
                </a>
            </div>
            <a href="index.php?page=contents/setting" class="nav-item nav-setting rounded-2 my-1">
                <span class=""><i class=""></i></span> Account Settings
            </a>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    
    toggleButtons.forEach(button => {
        const targetId = button.getAttribute('data-target');
        const targetSection = document.getElementById(targetId);
        
        button.classList.add('collapsed');
        
        button.addEventListener('click', function() {
            targetSection.classList.toggle('show');
            
            this.classList.toggle('collapsed');
        });
    });
    
    const page = '<?php echo isset($_GET["page"]) ? $_GET["page"] : "home"; ?>';
    const slug = page.split('/').pop(); 
    const navItem = document.querySelector('.nav-' + slug);
    if (navItem) {
        navItem.classList.add('active');
        
        const parentSection = navItem.closest('.toggle-section');
        if (parentSection) {
            parentSection.classList.add('show');
            const toggleBtn = document.querySelector(`[data-target="${parentSection.id}"]`);
            if (toggleBtn) {
                toggleBtn.classList.remove('collapsed');
            }
        }
    }
});
</script>