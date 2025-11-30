<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4><i class="fas fa-calendar-check"></i> Attendance Management</h4>
            <small class="text-muted">Track employee attendance, biometric data, and verification</small>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                <i class="fas fa-plus me-1"></i> Mark Attendance
            </button>
        </div>
    </div>

    <!-- Mark Attendance Modal -->
    <div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Mark Attendance</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="attendanceForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee</label>
                                <select class="form-select" id="employeeSelect" required>
                                    <option value="">Select Employee</option>
                                    <!-- Options will be populated via AJAX -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" id="attendanceDate" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time In</label>
                                <input type="time" class="form-control" id="timeIn">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time Out</label>
                                <input type="time" class="form-control" id="timeOut">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="attendanceStatus" required>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="halfday">Half Day</option>
                                    <option value="leave">On Leave</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="attendanceRemarks" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-2">
        <div class="col-md-4">
            <div class="card shadow bg-white text-center p-4">
                <h5 id="totalPresent">0</h5>
                <small>Present Today</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow bg-white text-center p-4">
                <h5 id="totalAbsent">0</h5>
                <small>Absent Today</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow bg-white text-center p-4">
                <h5 id="totalLate">0</h5>
                <small>Late Arrivals</small>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card">
        <div class="card-body d-flex flex-wrap justify-content-between col-md-12">
            <ul class="nav nav-tabs card-header-tabs" id="attendanceTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button">
                        <i class="fas fa-calendar-day me-2"></i>Daily Attendance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="biometric-tab" data-bs-toggle="tab" data-bs-target="#biometric" type="button">
                        <i class="fas fa-fingerprint me-2"></i>Biometric Enrollment
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="verification-tab" data-bs-toggle="tab" data-bs-target="#verification" type="button">
                        <i class="fas fa-user-edit me-2"></i>Verification
                    </button>
                </li>
            </ul>
            <div class="d-flex align-items-center col-md-5">
                <div class="w-100">
                    <input type="text" class="form-control" placeholder="Search..." id="payslipSearch">
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="tab-content" id="attendanceTabContent">
                <!-- Daily Attendance Tab -->
                <div class="tab-pane fade show active" id="daily" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" id="attendanceFilterDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select class="form-select" id="attendanceDepartmentFilter">
                                <option value="">All Departments</option>
                                <option value="IT">IT Department</option>
                                <option value="HR">HR Department</option>
                                <option value="Finance">Finance Department</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="attendanceStatusFilter">
                                <option value="">All Status</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="dailyAttendanceTable">
                                <tr>
                                    <td colspan="7" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Biometric Enrollment Tab -->
                <div class="tab-pane fade" id="biometric" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Enroll New Biometric</h6>
                                </div>
                                <div class="card-body">
                                    <form id="biometricEnrollmentForm">
                                        <div class="mb-3">
                                            <label class="form-label">Employee</label>
                                            <select class="form-select" id="biometricEmployee" required>
                                                <option value="">Select Employee</option>
                                                <!-- Options will be populated via AJAX -->
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Biometric Type</label>
                                            <select class="form-select" id="biometricType" required>
                                                <option value="fingerprint">Fingerprint</option>
                                                <option value="facial">Facial Recognition</option>
                                                <option value="iris">Iris Scan</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Biometric Data</label>
                                            <div class="border rounded p-3 text-center" style="height: 200px; background-color: #f8f9fa;">
                                                <p class="text-muted mt-4">Place finger on scanner</p>
                                                <div id="biometricPreview" class="d-none">
                                                    <!-- Preview will be shown here -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">Enroll Biometric</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Enrolled Biometrics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Type</th>
                                                    <th>Enrolled On</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="biometricEnrollmentsTable">
                                                <tr>
                                                    <td colspan="4" class="text-center">Loading data...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biometric Verification Tab -->
                <div class="tab-pane fade" id="verification" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Search Employee</label>
                            <input type="text" class="form-control" id="verificationSearch" placeholder="Employee name or ID">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="verificationStatusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="lost">Lost</option>
                                <option value="replaced">Replaced</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-primary w-100" id="refreshVerificationBtn">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Biometric Type</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="verificationTable">
                                <tr>
                                    <td colspan="6" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Biometric Modal -->
    <div class="modal fade" id="editBiometricModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Biometric Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editBiometricForm">
                        <input type="hidden" id="editBiometricId">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="editBiometricStatus" required>
                                <option value="active">Active</option>
                                <option value="lost">Lost</option>
                                <option value="replaced">Replaced</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" id="editBiometricRemarks" rows="3"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-danger me-2" id="deleteBiometricBtn">
                                <i class="fas fa-trash me-2"></i>Delete
                            </button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>