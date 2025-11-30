<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4><i class="fas fa-money-bill-wave"></i> Payroll Management</h4>
            <small class="text-muted">Manage Salary Deductions, Loans, and Leave Pay</small>
        </div>
        <div>
            <button class="btn btn-primary" id="addNewRecordBtn">
                <i class="fas fa-plus me-1"></i> Add New
            </button>
        </div>
    </div>

    <div class="modal fade" id="deductionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Salary Deduction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="deductionForm">
                        <input type="hidden" id="deductionId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee</label>
                                <select class="form-select" id="deductionEmployee" required>
                                    <option value="">Select Employee</option>
                                    <!-- Options will be populated via AJAX -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Deduction Type</label>
                                <select class="form-select" id="deductionType" required>
                                    <option value="">Select Type</option>
                                    <option value="tax">Tax</option>
                                    <option value="sss">SSS</option>
                                    <option value="philhealth">PhilHealth</option>
                                    <option value="pagibig">Pag-IBIG</option>
                                    <option value="advance">Salary Advance</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control" id="deductionAmount" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" id="deductionDate" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="deductionDescription" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Installment Plan</label>
                                <select class="form-select" id="deductionInstallment">
                                    <option value="1">One-time</option>
                                    <option value="2">2 Months</option>
                                    <option value="3">3 Months</option>
                                    <option value="6">6 Months</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="deductionStatus" required>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Deduction</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Modal -->
    <div class="modal fade" id="loanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Salary Loan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="loanForm">
                        <input type="hidden" id="loanId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee</label>
                                <select class="form-select" id="loanEmployee" required>
                                    <option value="">Select Employee</option>
                                    <!-- Options will be populated via AJAX -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Loan Type</label>
                                <select class="form-select" id="loanType" required>
                                    <option value="">Select Type</option>
                                    <option value="emergency">Emergency Loan</option>
                                    <option value="salary">Salary Loan</option>
                                    <option value="housing">Housing Loan</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Loan Amount</label>
                                <input type="number" class="form-control" id="loanAmount" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date Granted</label>
                                <input type="date" class="form-control" id="loanDate" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Interest Rate (%)</label>
                                <input type="number" class="form-control" id="loanInterest" step="0.01" value="5.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Term (Months)</label>
                                <input type="number" class="form-control" id="loanTerm" min="1" value="12">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Purpose</label>
                                <textarea class="form-control" id="loanPurpose" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Monthly Payment</label>
                                <input type="number" class="form-control" id="loanMonthly" step="0.01" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="loanStatus" required>
                                    <option value="active">Active</option>
                                    <option value="paid">Paid</option>
                                    <option value="defaulted">Defaulted</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Loan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Pay Modal -->
    <div class="modal fade" id="leavePayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Leave Pay</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="leavePayForm">
                        <input type="hidden" id="leavePayId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee</label>
                                <select class="form-select" id="leavePayEmployee" required>
                                    <option value="">Select Employee</option>
                                    <!-- Options will be populated via AJAX -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Leave Type</label>
                                <select class="form-select" id="leavePayType" required>
                                    <option value="">Select Type</option>
                                    <option value="vacation">Vacation Leave</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="maternity">Maternity Leave</option>
                                    <option value="paternity">Paternity Leave</option>
                                    <option value="bereavement">Bereavement Leave</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="leavePayStart" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" id="leavePayEnd" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Days</label>
                                <input type="number" class="form-control" id="leavePayDays" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Daily Rate</label>
                                <input type="number" class="form-control" id="leavePayRate" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Amount</label>
                                <input type="number" class="form-control" id="leavePayAmount" step="0.01" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="leavePayStatus" required>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="paid">Paid</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Reason</label>
                                <textarea class="form-control" id="leavePayReason" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Leave Pay</button>
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
                <h5 id="totalDeductions">₱0.00</h5>
                <small>Total Deductions This Month</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow bg-white text-center p-4">
                <h5 id="totalLoans">₱0.00</h5>
                <small>Total Loans This Month</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow bg-white text-center p-4">
                <h5 id="totalLeavePay">₱0.00</h5>
                <small>Total Leave Pay This Month</small>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card">
        <div class="card-body d-flex flex-wrap justify-content-between col-md-12">
            <ul class="nav nav-tabs card-header-tabs" id="payrollTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="deduction-tab" data-bs-toggle="tab" data-bs-target="#deduction"
                        type="button">
                        <i class="fas fa-minus-circle me-2"></i>Salary Deductions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="loan-tab" data-bs-toggle="tab" data-bs-target="#loan" type="button">
                        <i class="fas fa-hand-holding-usd me-2"></i>Salary Loans
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="leavePay-tab" data-bs-toggle="tab" data-bs-target="#leavePay"
                        type="button">
                        <i class="fas fa-umbrella-beach me-2"></i>Leave Pay
                    </button>
                </li>
            </ul>
            <div class="d-flex align-items-center col-md-4">
                <div class="w-100">
                    <input type="text" class="form-control" placeholder="Search..." id="payrollSearch">
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="tab-content" id="payrollTabContent">
                <!-- Deduction Tab -->
                <div class="tab-pane fade show active" id="deduction" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="deductionMonthFilter">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <!-- Other months -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="deductionYearFilter">
                                <option value="2023">2023</option>
                                <option value="2024" selected>2024</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="deductionTypeFilter">
                                <option value="">All Types</option>
                                <option value="tax">Tax</option>
                                <option value="sss">SSS</option>
                                <option value="philhealth">PhilHealth</option>
                                <option value="pagibig">Pag-IBIG</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" id="exportDeductionsBtn">
                                <i class="fas fa-file-export me-2"></i>Export
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="deduction_records">
                                <tr>
                                    <td colspan="6" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Loan Tab -->
                <div class="tab-pane fade" id="loan" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-select" id="loanStatusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="paid">Paid</option>
                                <option value="defaulted">Defaulted</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="loanTypeFilter">
                                <option value="">All Types</option>
                                <option value="emergency">Emergency</option>
                                <option value="salary">Salary</option>
                                <option value="housing">Housing</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100" id="exportLoansBtn">
                                <i class="fas fa-file-export me-2"></i>Export
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Loan Type</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                    <th>Term</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="loanTable">
                                <tr>
                                    <td colspan="7" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Leave Pay Tab -->
                <div class="tab-pane fade" id="leavePay" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="leavePayMonthFilter">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <!-- Other months -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="leavePayYearFilter">
                                <option value="2023">2023</option>
                                <option value="2024" selected>2024</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="leavePayStatusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" id="exportLeavePayBtn">
                                <i class="fas fa-file-export me-2"></i>Export
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="leavePayTable">
                                <tr>
                                    <td colspan="7" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>