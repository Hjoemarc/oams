// ================================= LOGOUT BUTTOn
   

// ================================= HR PROFILE (TABS)  
    document.addEventListener('DOMContentLoaded', function() {
        initProfileTabs();
    
    });

    function initProfileTabs() {
        const tabButtons = document.querySelectorAll('#ProfileInfoTabs .nav-link');
        const tabContents = document.querySelectorAll('.tab-pane');
        
        // Initialize tabs - hide all except active
        tabContents.forEach(content => {
            if (!content.classList.contains('active')) {
                content.style.display = 'none';
            }
        });
        
        tabButtons.forEach((button) => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active button
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.style.display = 'none';
                    content.classList.remove('show', 'active');
                });
                
                // Show the selected tab
                const targetId = this.getAttribute('data-bs-target');
                const targetContent = document.querySelector(targetId);
                if (targetContent) {
                    targetContent.style.display = 'block';
                    targetContent.classList.add('show', 'active');
                    
                    // Load data for the active tab
                    if (targetId === '#Employment') {
                        // loadEmployeeData_hr_pending();
                    } else if (targetId === '#Personal') {
                        // loadEmployeeData_hr();
                    } else if (targetId === '#Education') {
                        // loadEmployeeData_hr_rejected();
                    } else if (targetId === '#Family') {
                        // loadEmployeeData_hr_rejected();
                    }
                }
            });
        });
    }

// ================================= DEPARTMENTS AND JOB  (TABS)  
    document.addEventListener('DOMContentLoaded', function() {
        initDepartmentsJobTitlesTabs();
    });

    function initDepartmentsJobTitlesTabs() {
        const tabButtons = document.querySelectorAll('#DepartmentsJobsInfoTab .nav-link');
        const tabContents = document.querySelectorAll('.tab-pane');
        
        // Initialize tabs - hide all except active
        tabContents.forEach(content => {
            if (!content.classList.contains('active')) {
                content.style.display = 'none';
            }
        });
        
        tabButtons.forEach((button) => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active button
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.style.display = 'none';
                    content.classList.remove('show', 'active');
                });
                
                // Show the selected tab
                const targetId = this.getAttribute('data-bs-target');
                const targetContent = document.querySelector(targetId);
                if (targetContent) {
                    targetContent.style.display = 'block';
                    targetContent.classList.add('show', 'active');
                    
                    // Load data for the active tab
                    if (targetId === '#JobTitlesInfno') {
                        
                    } else if (targetId === '#departmentsInfo') {
                        
                    } else if (targetId === '#CareerPathsInfo') {
                        loadCareerPathData();
                    } 
                }
            });
        });
    }

    // DEPARTMENTS ============================================
    function edit_department(id, name, code) {
        // Set the values directly (bypassing the fetch for now)
        document.getElementById('editDepartmentId').value = id;
        document.getElementById('editDepartmentName').value = name;
        document.getElementById('editDepartmentCode').value = code;

        console.log('Modal values set:',
            document.getElementById('editDepartmentId').value,
            document.getElementById('editDepartmentName').value,
            document.getElementById('editDepartmentCode').value
        );
        // Show the modal
        var editModal = new bootstrap.Modal(document.getElementById('editdepartmentsModal'));
        editModal.show();
    }
    // onclicked function Delete
    function setDeleteDepartmentId(id) {
        
        document.getElementById('deleteDepartmentId').value = id
    }
    // JOB TITLES ============================================

    function edit_jobTitle(id, program_name, program_code, department_id) {
        // Set the values in the modal
        document.getElementById('editProgram_id').value = id;
        document.getElementById('program_name').value = program_name;
        document.getElementById('program_code').value = program_code;
        document.getElementById('department_ids').value = department_id;
        
         console.log('Modal values set:',
            document.getElementById('editDepartmentId').value,
            document.getElementById('editDepartmentName').value,
            document.getElementById('editDepartmentCode').value
        );
        var editModal = new bootstrap.Modal(document.getElementById('editJobTitlesModal'));
        editModal.show();
    }
    function setDeletejobtTitleId(id) {
        document.getElementById('deleteJobTitleId').value = id;
    }