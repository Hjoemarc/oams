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

    function edit_jobTitle(id, title, salary) {
        // Set the values in the modal
        document.getElementById('editUserIdEdit').value = id;
        document.getElementById('jobTitle').value = title;
        document.getElementById('salary').value = salary;
        
        var editModal = new bootstrap.Modal(document.getElementById('editJobTitlesModal'));
        editModal.show();
    }
    function setDeletejobtTitleId(id) {
        document.getElementById('deleteJobTitleId').value = id;
    }

    // CAREER PATHS ============================================
    // onclick button for types
    
    function loadCareerPathData() {
        $.ajax({
            url: base_url + "authentication/action.php?action=fetch_careerPaths_data",
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#careerPathEmployees').html('<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary" role="status"></div></tr>');
            },
            success: function(response) {
                if (response?.status === 1) {
                    populateCareerPathTable(response.data);
                } else {
                    showTableError('#careerPathEmployees', response?.message || 'Invalid response format');
                }
            },
            error: function(xhr) {
                showTableError('#careerPathEmployees', `Error: ${xhr.statusText} (${xhr.status})`);
            }
        });
    }

    function fetchEmployeeCareerPath(employeeIdCareerPath) {
        const modalBody = document.querySelector('#viewCareerPath .modal-body');
        modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>';
        
        $.ajax({
            url: base_url + "authentication/action.php?action=fetch_careerPath_data&employeeIdCareerPath",
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'fetch_careerPath_data',
                employee_id: employeeIdCareerPath
            },
            success: function(response) {
                console.log('API Response:', response);
                
                if (response?.status === 1) {
                    if (response.data) {
                        populateModalCareerPath(response.data);
                    } else {
                        modalBody.innerHTML = '<div class="alert alert-warning">No career history found</div>';
                    }
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">${response?.message || 'Request failed'}</div>`;
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {status, error, response: xhr.responseText});
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        Error loading career history: ${status}<br>
                        <small>${xhr.responseText?.substring(0, 100) || 'No details available'}</small>
                    </div>
                `;
            }
        });
    }

    function populateModalCareerPath(data) {
        console.log('Career Path Data:', data);
        const modalBody = document.querySelector('#viewCareerPath .modal-body');
        
        if (!data?.employee || !data.employee.employee_id) {
            modalBody.innerHTML = '<div class="alert alert-danger">Invalid employee data</div>';
            return;
        }

        const employee = data.employee;
        const history = data.history || [];
        
        let html = `
        `;

        if (history.length > 0) {
            html += `
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>From Position</th>
                                <th>To Position</th>
                                <th>Change Type</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            history.forEach(item => {
                html += `
                    <tr>
                        <td>${item.job_from || 'N/A'}</td>
                        <td>${item.job_to || 'N/A'}</td>
                        <td>${item.job_status || 'N/A'}</td>
                        <td>${item.change_date ? formatDateString(item.change_date) : 'N/A'}</td>
                    </tr>
                `;
            });

            // Helper function
            function formatDateString(dateString) {
                try {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                } catch (e) {
                    console.error('Error formatting date:', e);
                    return dateString; // Return original if formatting fails
                }
            }
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            html += '<p class="text-muted">No career history records found</p>';
        }

        html += `
                </div>
            </div>
        `;

        modalBody.innerHTML = html;
    }

    function populateCareerPathTable(careerPaths) { 
        const tbody = $('#careerPathEmployees');
        tbody.empty();
        
        if (!careerPaths?.length) {
            tbody.html('<tr><td colspan="7" class="text-center">No career paths found</td></tr>');
            return;
        }
        
        careerPaths.forEach((careerPath, index) => {
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${careerPath.employeeID}</td>
                    <td>${careerPath.firstname} ${careerPath.lastname}</td>
                    <td>${careerPath.department}</td>
                    <td>${careerPath.jobTitle}</td>
                    <td>${careerPath.salary}</td>
                    <td>
                        <button type="button" class="btn px-3 btn-sm btn-danger manage-btn"
                            data-employee-id="${careerPath.employee_id}"
                            data-jobtitle-id="${careerPath.jobTitles_id}"
                            data-jobtitle="${encodeURIComponent(careerPath.jobTitle)}"
                            data-salary="${careerPath.salary}">
                            <i class="fas fa-edit"></i> Manage
                        </button>
                        <button type="button" class="btn btn-sm px-3 btn-dark"
                                onclick="fetchEmployeeCareerPath('${careerPath.employee_id}')"
                                data-bs-toggle="modal" 
                                data-bs-target="#viewCareerPath">
                            <i class="fas fa-history"></i> History
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    $(document).on('click', '.manage-btn', function() {
        const employee_id = $(this).data('employee-id');
        const jobTitles_id = $(this).data('jobtitle-id');
        const jobTitle = decodeURIComponent($(this).data('jobtitle'));
        const salary = $(this).data('salary');

        manage_careerPath(employee_id, jobTitles_id, jobTitle, salary);
    });


    function manage_careerPath(employeeId, jobTitleId, jobTitle, salary) {
        // Set the values in the modal
        document.getElementById('editEmployeeId').value = employeeId;
        document.getElementById('currentJobTitleId').value = jobTitleId;
        document.getElementById('currentJobTitle').value = jobTitle;
        document.getElementById('currentSalary').value = salary;
        
        // Update the dropdown and salary field when selection changes
        document.getElementById('jobTitleSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const salary = selectedOption.text.match(/\(([^)]+)\)/)[1];
                document.getElementById('newSalary').value = salary;
            } else {
                document.getElementById('newSalary').value = '';
            }
        });
        
        var manageModal = new bootstrap.Modal(document.getElementById('manageCareerPath'));
        manageModal.show();
    }
    function formatFullNameCareerPath(employee, lastNameFirst = false) {
        const { firstname, lastname, suffix } = employee;
        if (lastNameFirst) {
            return `${lastname}${suffix ? ', ' + suffix : ''}, ${firstname}`;
        }
        return `${firstname} ${lastname}${suffix ? ' ' + suffix : ''}`;
    }

 // ================================= Leave (TABS)
document.addEventListener('DOMContentLoaded', function() {
    initTabs();
    // Load pending leave requests by default (first tab)
   
});

function initTabs() {
    const tabButtons = document.querySelectorAll('#LeaveRequestTabs .nav-link');
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
                if (targetId === '#Pending_Leave') {
                } else if (targetId === '#Approved_leave') {
                } else if (targetId === '#Rejected_Leave') {
                }
            }
        });
    });
}

// PENDING LEAVE REQUESTS
function loadLeaveData_pending() {
    $.ajax({
        url: base_url + "authentication/action.php?action=fetch_leave_requests_pending",
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#Leave_Pending').html('<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"></div></td></tr>');
        },
        success: function(response) {
            if (response?.status === 1) {
                populateLeaveTable_pending(response.data);
            } else {
                showTableError('#Leave_Pending', response?.message || 'Invalid response format');
            }
        },
        error: function(xhr) {
            showTableError('#Leave_Pending', `Error: ${xhr.statusText} (${xhr.status})`);
        }
    });
}

function populateLeaveTable_pending(leaves) {
    const tbody = $('#Leave_Pending');
    tbody.empty();

    if (!leaves?.length) {
        tbody.html('<tr><td colspan="6" class="text-center">No pending leave requests found</td></tr>');
        return;
    }

    leaves.forEach((leave, index) => {
        const fullName = `${leave.firstname || ''} ${leave.middlename || ''} ${leave.lastname || ''} ${leave.suffix || ''}`.trim();
        const dateRange = `${formatDate(leave.InclusiveFrom)} - ${formatDate(leave.InclusiveTo)}`;
        const statusBadge = getStatusBadge(leave.leaveStatus);

        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${fullName}</td>
                <td>${leave.leaveType}</td>
                <td>${dateRange}</td>
                <td>${statusBadge}</td>
                <td class="text-center">
                    <a href="index.php?page=contents/viewLeave&leave_id=${leave.leave_id}"><button class="btn btn-sm btn-primary px-3 view-leave-details">
                        <i class="fas fa-eye"></i> View
                    </button></a>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Helper functions
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function getStatusBadge(status) {
    const statusMap = {
        'pending': 'warning',
        'approved': 'success',
        'disapproved': 'danger'
    };
    
    const badgeClass = statusMap[status.toLowerCase()] || 'secondary';
    const statusText = status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
    
    return `<span class="badge bg-${badgeClass}">${statusText}</span>`;
}

function showTableError(selector, message) {
    $(selector).html(`<tr><td colspan="6" class="text-center text-danger">${message}</td></tr>`);
}
// end of pending leave detailes =======================================================

// APPROVED LEAVE REQUESTS
function loadLeaveData_approved() {
    $.ajax({
        url: base_url + "authentication/action.php?action=fetch_leave_requests_approved",
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#official_LeaveRquest').html('<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"></div></td></tr>');
        },
        success: function(response) {
            if (response?.status === 1) {
                populateLeaveTable_approved(response.data);
            } else {
                showTableError('#official_LeaveRquest', response?.message || 'Invalid response format');
            }
        },
        error: function(xhr) {
            showTableError('#official_LeaveRquest', `Error: ${xhr.statusText} (${xhr.status})`);
        }
    });
}

function populateLeaveTable_approved(leaves) {
    const tbody = $('#official_LeaveRquest');
    tbody.empty();
    
    if (!leaves?.length) {
        tbody.html('<tr><td colspan="6" class="text-center">No approved leave requests found</td></tr>');
        return;
    }
    
    leaves.forEach((leave, index) => {
        const fullName = `${leave.firstname || ''} ${leave.middlename || ''} ${leave.lastname || ''} ${leave.suffix || ''}`.trim();
        const dateRange = `${formatDate(leave.InclusiveFrom)} - ${formatDate(leave.InclusiveTo)}`;
        const statusBadge = getStatusBadge(leave.leaveStatus);
        
        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${fullName}</td>
                <td>${leave.leaveType}</td>
                <td>${dateRange}</td>
                <td >${statusBadge}</td>
                <td class="text-center">
                    <a href="index.php?page=contents/reviewLeave&leave_id=${leave.leave_id}" ><button class="btn btn-sm btn-danger px-3 view-leave-details" data-id="${leave.leave_id}">
                        <i class="fas fa-eye"></i> View
                    </button></a>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Add event listeners for view buttons
    $('.view-leave-details').click(function() {
        const leaveId = $(this).data('id');
        viewLeaveDetails(leaveId);
    });
}

// REJECTED LEAVE REQUESTS
function loadLeaveData_rejected() {
    $.ajax({
        url: base_url + "authentication/action.php?action=fetch_leave_requests_rejected",
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#Reject_LeaveRequest').html('<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"></div></td></tr>');
        },
        success: function(response) {
            if (response?.status === 1) {
                populateLeaveTable_rejected(response.data);
            } else {
                showTableError('#Reject_LeaveRequest', response?.message || 'Invalid response format');
            }
        },
        error: function(xhr) {
            showTableError('#Reject_LeaveRequest', `Error: ${xhr.statusText} (${xhr.status})`);
        }
    });
}

function populateLeaveTable_rejected(leaves) {
    const tbody = $('#Reject_LeaveRequest');
    tbody.empty();
    
    if (!leaves?.length) {
        tbody.html('<tr><td colspan="6" class="text-center">No rejected leave requests found</td></tr>');
        return;
    }
    
    leaves.forEach((leave, index) => {
        const fullName = `${leave.firstname || ''} ${leave.middlename || ''} ${leave.lastname || ''} ${leave.suffix || ''}`.trim();
        const dateRange = `${formatDate(leave.InclusiveFrom)} - ${formatDate(leave.InclusiveTo)}`;
        const statusBadge = getStatusBadge(leave.leaveStatus);
        
        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${fullName}</td>
                <td>${leave.leaveType}</td>
                <td>${dateRange}</td>
                <td>${statusBadge}</td>
                <td class="text-center">
                    <a href="index.php?page=contents/ReviewLeave&leave_id=${leave.leave_id}" ><button class="btn btn-sm btn-danger px-3 view-leave-details" data-id="${leave.leave_id}">
                        <i class="fas fa-eye"></i> View
                    </button></a>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    $('.view-leave-details').click(function() {
        const leaveId = $(this).data('id');
        viewLeaveDetails(leaveId);
    });
}

// HELPER FUNCTIONS
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function getStatusBadge(status) {
    const statusMap = {
        'Pending': 'info',
        'Recommended': 'success',
        'Disapproved': 'danger'
    };
    
    const badgeClass = statusMap[status] || 'secondary';
    return `<span class="badge bg-${badgeClass}">${status}</span>`;
}

function showTableError(selector, message) {
    $(selector).html(`<tr><td colspan="6" class="text-center text-danger">${message}</td></tr>`);
}

// LEAVE ACTIONS
function viewLeaveDetails(leaveId) {
    console.log('View leave details:', leaveId);
}
