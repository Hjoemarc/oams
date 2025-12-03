
<nav id="sidebar" class="navbarHide">
    <div style="width: 240px; border-radius: 5px;">
        <div class="sidebar-list m-2">
            <a href="index.php?page=home" class="nav-item nav-home rounded-2">
                <span class=""><i class=""></i></span> Dashboard
            </a>
            
            <!-- System Management Section -->
            <button class="toggle-btn collapsed rounded-2" data-target="system-management-section">
                System Management <i class="toggle-icon"><i class="fa-solid fa-caret-down"></i></i>
            </button>
            <div id="system-management-section" class="toggle-section">
                <a href="index.php?page=contents/users" class="nav-item nav-users my-1 rounded-2">
                    <span class=""><i class="fa-solid fa-users"></i></span> User Management
                </a>
                <a href="index.php?page=contents/program" class="nav-item nav-program my-1 rounded-2">
                    <span class=""><i class="fa-solid fa-graduation-cap"></i></span> Program Management
                </a>
                <a href="index.php?page=contents/accreditation_framework" class="nav-item nav-accreditation_framework my-1 rounded-2">
                    <span class=""><i class="fa-solid fa-clipboard-list"></i></span> Accreditation Framework
                </a>
            </div>
            
            <!-- Accreditation Operations Section -->
            <button class="toggle-btn collapsed rounded-2" data-target="accreditation-operations-section">
                Accreditation Operations <i class="toggle-icon"><i class="fa-solid fa-caret-down"></i></i>
            </button>
            <div id="accreditation-operations-section" class="toggle-section">
                <a href="index.php?page=contents/accreditation_cycles" class="nav-item nav-accreditation_cycles my-1 rounded-2">
                    <span class=""><i class="fa-solid fa-calendar-alt"></i></span> Accreditation Cycles
                </a>
                <a href="index.php?page=contents/document_management" class="nav-item nav-document_management my-1 rounded-2">
                    <span class=""><i class="fa-solid fa-folder-open"></i></span> Document Management
                </a>
                <a href="index.php?page=contents/compliance_tracking" class="nav-item nav-compliance_tracking my-1 rounded-2">
                    <span class=""><i class="fa-solid fa-chart-line"></i></span> Compliance Tracking
                </a>
                <a href="index.php?page=contents/review_management" class="nav-item nav-review_management my-1 rounded-2">
                    <span class=""><i class="fa-solid fa-magnifying-glass"></i></span> Review Management
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