
<nav id="sidebar" class="navbarHide">
    <div style="width: 240px; border-radius: 5px;">
        <div class="sidebar-list m-2">
            <a href="index.php?page=home" class="nav-item nav-home rounded-2">
                <span class=""><i class=""></i></span> Dashboard
            </a>
            <a href="index.php?page=contents/assignments" class="nav-item nav-assignments rounded-2 my-1">
                <span class=""><i class=""></i></span> My Assignments
            </a>
            <a href="index.php?page=contents/documment" class="nav-item nav-documment rounded-2 my-1">
                <span class=""><i class=""></i></span> Documment Management
            </a>
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