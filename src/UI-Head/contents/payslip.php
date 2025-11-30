<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4 class=""><i class="fa-solid fa-peso-sign"></i> Payslip Management</h4>
            <small class="text-muted">Manage Employee Payslips, Deductions, and Reports</small>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generatePayslipModal">
                <i class="fas fa-plus me-1"></i> Generate Payslips
            </button>
        </div>
    </div>

    <!-- Generate Payslip Modal -->
    <div class="modal fade" id="generatePayslipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Generate Payslips</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="generatePayslipForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pay Period</label>
                                <select class="form-select" id="payPeriod" required>
                                    <option value="">Select Period</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="bi-monthly">Bi-Monthly</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date Range</label>
                                <input type="text" class="form-control date-range-picker" placeholder="Select date range">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="departmentFilter">
                                    <option value="">All Departments</option>
                                    <option value="IT">IT Department</option>
                                    <option value="HR">HR Department</option>
                                    <option value="Finance">Finance Department</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee Status</label>
                                <select class="form-select" id="employeeStatus">
                                    <option value="">All Employees</option>
                                    <option value="regular">Regular</option>
                                    <option value="contractual">Contractual</option>
                                    <option value="probationary">Probationary</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Generate Payslips</button>
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
                <h5 id="totalPayslips">0</h5>
                <small>Total Payslips Generated</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow bg-white text-center p-4">
                <h5 id="totalPayroll">₱0.00</h5>
                <small>Total Payroll Amount</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow bg-white text-center p-4">
                <h5 id="pendingPayslips">0</h5>
                <small>Pending Approvals</small>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card">
        <div class="card-body d-flex flex-wrap justify-content-between col-md-12 col-12">
            <ul class="nav nav-tabs card-header-tabs" id="payslipTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 m-0 py-2 active" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual" type="button">
                        <i class="fas fa-user me-2"></i>Individual Payslips
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 m-0 py-2" id="records-tab" data-bs-toggle="tab" data-bs-target="#records" type="button">
                        <i class="fas fa-clipboard-list me-2"></i>Payslip Records
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 m-0 py-2" id="comparison-tab" data-bs-toggle="tab" data-bs-target="#comparison" type="button">
                        <i class="fas fa-chart-bar me-2"></i>Payroll Comparison
                    </button>
                </li>
            </ul>
            <div class="d-flex align-items-center col-md-4">
                <div class="w-100">
                    <input type="text" class="form-control" placeholder="Search..." id="payslipSearch">
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="tab-content" id="payslipTabContent">
                <!-- Individual Payslips Tab -->
                <div class="tab-pane fade show active" id="individual" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Pay Period</th>
                                    <th>Net Pay</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="individualPayslipTable">
                                <!-- Data will be loaded via AJAX -->
                                <tr>
                                    <td colspan="8" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                </div>

                <!-- Payslip Records Tab -->
                <div class="tab-pane fade" id="records" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="recordsYearFilter">
                                <option value="">All Years</option>
                                <option value="2023">2023</option>
                                <option value="2024" selected>2024</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="recordsMonthFilter">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <!-- ... other months ... -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="recordsStatusFilter">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" id="exportRecordsBtn">
                                <i class="fas fa-file-export me-2"></i>Export
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive col-md-12">
                        <table class="table table-bordered table-hover col-md-12" >
                            <thead class="table-light col-md-12">
                                <tr class="col-md-12">
                                    <!-- <th class="col-md-1">Payslip ID</th> -->
                                    <th class="col-md-2">Pay Period</th>
                                    <th class="col-md-1">Total Employees</th>
                                    <th class="col-md-2">Total Gross Pay</th>
                                    <th class="col-md-2">Total Deductions</th>
                                    <th class="col-md-1">Total Net Pay</th>
                                    <th class="col-md-1">Generated On</th>
                                    <th class="col-md-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="payslipRecordsTable">
                                <!-- Data will be loaded via AJAX -->
                                <tr>
                                    <td colspan="8" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payroll Comparison Tab -->
                <div class="tab-pane fade" id="comparison" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-select" id="comparisonType">
                                <option value="monthly">Monthly Comparison</option>
                                <option value="yearly">Yearly Comparison</option>
                                <option value="department">Department Comparison</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="comparisonYear">
                                <option value="2023">2023</option>
                                <option value="2024" selected>2024</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100" id="generateReportBtn">
                                <i class="fas fa-chart-line me-2"></i>Generate Report
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <canvas id="payrollChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Summary</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Highest Payroll Month:</td>
                                            <td class="text-end"><strong>December 2023</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Lowest Payroll Month:</td>
                                            <td class="text-end"><strong>January 2023</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Average Monthly Payroll:</td>
                                            <td class="text-end"><strong>₱1,245,678.00</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Year-to-Date Total:</td>
                                            <td class="text-end"><strong>₱14,948,136.00</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-responsive col-md-12">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Period</th>
                                                    <th class="text-end">Gross Pay</th>
                                                    <th class="text-end">Deductions</th>
                                                    <th class="text-end">Net Pay</th>
                                                    <th class="text-end">% Change</th>
                                                </tr>
                                            </thead>
                                            <tbody id="comparisonTable" class="col-md-12">
                                                <!-- Data will be loaded via AJAX -->
                                                <tr>
                                                    <td colspan="5" class="text-center">Loading data...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Payslip Modal -->
    <div class="modal fade" id="viewPayslipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Employee Payslip</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="payslip-container">
                        <div class="payslip-header text-center mb-4">
                            <h3>COMPANY NAME</h3>
                            <p>123 Business Address, City, Country</p>
                            <h4 class="mt-3">PAYSLIP</h4>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th>Employee ID:</th>
                                        <td id="ps-employee-id">EMP-001</td>
                                    </tr>
                                    <tr>
                                        <th>Employee Name:</th>
                                        <td id="ps-employee-name">John Doe</td>
                                    </tr>
                                    <tr>
                                        <th>Department:</th>
                                        <td id="ps-department">IT Department</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th>Pay Period:</th>
                                        <td id="ps-pay-period">January 1-31, 2024</td>
                                    </tr>
                                    <tr>
                                        <th>Payslip #:</th>
                                        <td id="ps-payslip-id">PS-2024-001</td>
                                    </tr>
                                    <tr>
                                        <th>Pay Date:</th>
                                        <td id="ps-pay-date">January 31, 2024</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Earnings</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th class="text-end">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ps-earnings">
                                                <tr>
                                                    <td>Basic Salary</td>
                                                    <td class="text-end">₱25,000.00</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-light">
                                                    <th>Total Earnings</th>
                                                    <th class="text-end" id="ps-total-earnings">₱25,000.00</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Deductions</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th class="text-end">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ps-deductions">
                                                <tr>
                                                    <td>Tax</td>
                                                    <td class="text-end">₱2,500.00</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-light">
                                                    <th>Total Deductions</th>
                                                    <th class="text-end" id="ps-total-deductions">₱2,500.00</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Gross Pay:</th>
                                        <td class="text-end" id="ps-gross-pay">₱25,000.00</td>
                                    </tr>
                                    <tr>
                                        <th>Total Deductions:</th>
                                        <td class="text-end" id="ps-total-deductions-summary">₱2,500.00</td>
                                    </tr>
                                    <tr class="table-active">
                                        <th>Net Pay:</th>
                                        <td class="text-end" id="ps-net-pay">₱22,500.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printPayslipBtn">
                        <i class="fas fa-print me-2"></i>Print Payslip
                    </button>
                    <button type="button" class="btn btn-success" id="sendPayslipBtn">
                        <i class="fas fa-paper-plane me-2"></i>Send to Employee
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
