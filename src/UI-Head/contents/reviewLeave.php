<?php
$leave_id = $_GET["leave_id"];
$stmtGetLEave = $pdo->prepare("
    SELECT 
        lr.*,
        ed.firstname,
        ed.middlename,
        ed.lastname,
        ed.suffix,
        ed.employee_id,
        jt.jobTitle,
        d.Department_name AS department,
        lc.VacationBalance,
        lc.SickBalance,
        lc.SpecialBalance,
        lc.OthersBalance
    FROM leaveReq lr
    INNER JOIN employee_data ed ON lr.employee_id = ed.employee_id
    LEFT JOIN hr_data hr ON lr.employee_id = hr.employee_id
    LEFT JOIN jobTitles jt ON hr.jobtitle_id = jt.jobTitles_id
    LEFT JOIN Departments d ON hr.Department_id = d.Department_id
    LEFT JOIN leaveCounts lc ON ed.employee_id = lc.employee_id
    WHERE lr.leave_id = ?
");
$stmtGetLEave->execute([$leave_id]);
$leave = $stmtGetLEave->fetch(PDO::FETCH_ASSOC);
$employee_id = $leave["employee_id"] ?? '';

// Debug: Check what data is actually returned
echo "<!-- Debug: " . print_r($leave, true) . " -->";
?>

<!-- Confirmation Modal -->
<div class="modal fade" id="updateModalEBG" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-start">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="updateModalLabel">Confirm Leave Action</h5>
            </div>
            <div class="modalConfirmation px-3 py-4 text-center">
                <h5 class="mb-0">Are you sure you want to process this leave request?</h5>
                <p class="text-muted mt-2">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="confirmSubmitBtn">Yes, Submit</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal-content">
    <!-- FORM STARTS HERE - WRAPS ENTIRE MODAL CONTENT -->
    <form class="row g-3 w-100" id="leaveProcess-form" method="POST">
        <div class="modal-header bg-primary text-white ms-2">
            <h5 class="modal-title text-white" id="leaveDetailsModalLabel">Leave Request Details</h5>
            <button type="button" class="btn btn-info m-0">Create PDF</button>
        </div>

        <div class="modal-body row w-100 scroll leave-height ms-2">
            <!-- Hidden Fields -->
            <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
            <input type="hidden" name="leave_id" id="leave_id" value="<?= htmlspecialchars($leave["leave_id"]) ?>">

            <h5 class="w-100 text-center m-0">ZAMBOANGA PUERICULTURE CENTER ORG. NO.144 INC.</h5>
            <strong class="w-100 text-center m-0">APPLICATION FOR LEAVE</strong>

            <!-- Your existing form content -->
            <div class="w-100 p-0 m-0 row name-section">
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" readonly name="lastname" value="<?= htmlspecialchars($leave["lastname"]) ?>"
                        class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">First Name</label>
                    <input type="text" readonly name="firstname" value="<?= htmlspecialchars($leave["firstname"]) ?>"
                        class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">M.I.</label>
                    <input type="text" readonly name="middlename" value="<?= htmlspecialchars($leave["middlename"]) ?>"
                        class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of filing <span class="text-danger">(required)</span></label>
                    <input type="date" readonly name="leaveDate" value="<?= htmlspecialchars($leave["leaveDate"]) ?>"
                        class="form-control">
                </div>
            </div>
            <div class="w-100 p-0 m-0 row name-section">
                <div class="col-md-6">
                    <label class="form-label">POSITION</label>
                    <input type="text" readonly name="position" value="<?= htmlspecialchars($leave["jobTitle"]) ?>"
                        class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">DEPARTMENT/SECTION</label>
                    <input type="text" readonly name="department" value="<?= htmlspecialchars($leave["department"]) ?>"
                        class="form-control">
                </div>
            </div>
            <div class="w-100 p-0 m-0 row name-section">
                <div class="col-md-2">
                    <label class="form-label">LEAVE APPLIED FOR <span class="text-danger">(required)</span></label>
                </div>

                <div class="col-md-3 d-flex">
                    <input type="radio" name="leaveType" value="Vacation_leave"
                        <?= $leave["leaveType"] == "Vacation_leave" ? "checked" : "" ?> disabled>
                    <span class="d-flex align-items-center ms-2">Vacation Leave</span>
                </div>
                <div class="col-md-2 d-flex">
                    <input type="radio" name="leaveType" value="Sick_leave"
                        <?= $leave["leaveType"] == "Sick_leave" ? "checked" : "" ?> disabled>
                    <span class="d-flex align-items-center ms-2">Sick Leave</span>
                </div>
                <div class="col-md-3 d-flex">
                    <input type="radio" name="leaveType" value="Special_leave"
                        <?= $leave["leaveType"] == "Special_leave" ? "checked" : "" ?> disabled>
                    <span class="d-flex align-items-center ms-2">Special Leave</span>
                </div>
                <div class="col-md-2 d-flex">
                    <input type="radio" name="leaveType" value="Others" id="leaveTypeOthers"
                        <?= $leave["leaveType"] == "Special_leave" ? "checked" : "" ?> disabled>
                    <span class="d-flex align-items-center ms-2">Others</span>
                </div>
            </div>
            <div class="col-md-12">
                <label class="form-label">Others Specify</label>
                <input type="text" readonly name="Others" value="<?= htmlspecialchars($leave["Others"]) ?>"
                    class="form-control">
            </div>
            <div class="col-md-12">
                <label class="form-label">COURSE/PURPOSE <span class="text-danger">(required)</span></label>
                <input type="text" readonly name="Purpose" value="<?= htmlspecialchars($leave["Purpose"]) ?>"
                    class="form-control">
            </div>
            <div class="w-100 p-0 m-0 row name-section">
                <div class="col-md-4">
                    <label class="form-label">INCLUSIVE DATE FROM: <span class="text-danger">(required)</span></label>
                    <input type="date" readonly name="InclusiveFrom"
                        value="<?= htmlspecialchars($leave["InclusiveFrom"]) ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">INCLUSIVE DATES TO: <span class="text-danger">(required)</span></label>
                    <input type="date" readonly name="InclusiveTo" value="<?= htmlspecialchars($leave["InclusiveTo"]) ?>"
                        class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NO. OF DAYS <span class="text-danger">(required)</span></label>
                    <input type="number" readonly name="numberOfDays"
                        value="<?= htmlspecialchars($leave["numberOfDays"]) ?>" class="form-control">
                </div>
            </div>
            <div class="col-md-12">
                <label class="form-label">CONTACT NO. WHILE ON LEAVE <span class="text-danger">(required)</span></label>
                <input type="text" readonly name="contact" value="<?= htmlspecialchars($leave["contact"]) ?>"
                    class="form-control">
            </div>
            <p class="text-start w-100 text-dark">I hereby pledge to report for work immediately the
                following day after expiration of my approved leave of absence unless
                otherwise duly extended. My failure to do so shall subject me to disciplinary action</p>
            <strong class="col-md-6">Recommending Approval:</strong>
            <div class="col-md-6 d-flex flex-column justify-content-center align-item-center">
                <p class="w-100" style="border-bottom: solid 1px #000; transform: translateY(15px);"></p>
                <span class="w-100 text-center m-0">Signature of Applicant</span>
            </div>
            <div class="col-md-12 row">
                <div class="col-md-6">
                    <label class="form-label">Section Head</label>
                    <input type="text" readonly name="sectionHead"
                        value="<?= htmlspecialchars($leave["sectionHead"]) ?>" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Department Head</label>
                    <input type="text" readonly name="departmentHead"
                        value="<?= htmlspecialchars($leave["departmentHead"]) ?>" class="form-control">
                </div>
            </div>

            <!-- Admin Only Section -->
            <div id="approvalContent" class="approvalContent col-md-12 col-11 p-0 m-0 mt-4 d-flex flex-column h-auto"
                style="border:solid 1px #000;">
                <h5 class="text-center my-1" style="border-bottom:solid 1px #000;">DETAILS OF ACTION ON APPLICATION
                </h5>

                <table id="admin-print-table" class="table table-bordered table-sm mb-0">
                    <thead class="text-center">
                        <tr>
                            <th style="width: 15.3rem;"></th>
                            <th style="width: 25%">VACATION</th>
                            <th style="width: 25%">SICK</th>
                            <th style="width: 25%">SPECIAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Balance as of</td>
                            <td><input type="text" name="vacationBalance" class="form-control p-1"
                                    value="<?= htmlspecialchars($leave["VacationBalance"] ?? '0') ?> DAYS"></td>
                            <td><input type="text" name="sickBalance" class="form-control p-1"
                                    value="<?= htmlspecialchars($leave["SickBalance"] ?? '0') ?> DAYS"></td>
                            <td><input type="text" name="specialBalance" class="form-control p-1"
                                    value="<?= htmlspecialchars($leave["SpecialBalance"] ?? '0') ?> DAYS"></td>
                        </tr>
                        <tr>
                            <td>Leave Earned</td>
                            <td><input type="text" name="vacationEarned" class="form-control p-1" value="+0"></td>
                            <td><input type="text" name="sickEarned" class="form-control p-1" value="+0"></td>
                            <td><input type="text" name="specialEarned" class="form-control p-1" value="+0"></td>
                        </tr>
                        <tr>
                            <td>Total Leave Credits as of</td>
                            <td><input type="text" name="vacationCredits" class="form-control p-1"
                                    value="<?= htmlspecialchars($leave["VacationBalance"] ?? '0') ?>"></td>
                            <td><input type="text" name="sickCredits" class="form-control p-1"
                                    value="<?= htmlspecialchars($leave["SickBalance"] ?? '0') ?>"></td>
                            <td><input type="text" name="specialCredits" class="form-control p-1"
                                    value="<?= htmlspecialchars($leave["SpecialBalance"] ?? '0') ?>"></td>
                        </tr>
                        <tr>
                            <td>Less this Leave</td>
                            <td><input type="text" name="vacationLessLeave" class="form-control p-1" value="0"></td>
                            <td><input type="text" name="sickLessLeave" class="form-control p-1" value="0"></td>
                            <td><input type="text" name="specialLessLeave" class="form-control p-1" value="0"></td>
                        </tr>
                        <tr>
                            <td>Balance to Date</td>
                            <td><input type="number" name="vacationBalanceToDate" class="form-control p-1"
                                    value="<?= htmlspecialchars($leave["VacationBalance"] ?? '0') ?>"></td>
                            <td><input type="number" name="sickBalanceToDate" class="form-control p-1"
                                    value="<?= htmlspecialchars($leave["SickBalance"] ?? '0') ?>"></td>
                            <td><input type="number" name="specialBalanceToDate" class="form-control p-1"
                                    value="<?= htmlspecialchars($leave["SpecialBalance"] ?? '0') ?>"></td>
                        </tr>
                    </tbody>
                </table>

                <div id="admin-screen-controls" class="recommendation col-md-12 col-12 mt-4">
                    <div class="d-flex flex-column col-md-12 col-11">
                        <label for="" class="fw-bold ms-3">Recommendation for:</label>
                        <div
                            class="row d-flex col-md-11 col-11 flex-row justify-content-start align-items-center m-0 p-0">
                            <input type="radio" class="col-md-1 col-1" id="approved" name="leaveStatus"
                                value="Approved" <?= $leave["leaveStatus"] == "approved" ? "checked" : "" ?>>
                            <label class="col-md-1 col-1 text-start" for="approved">Approved</label>
                        </div>
                        <div
                            class="row d-flex col-md-11 col-11 flex-row justify-content-start align-items-center m-0 p-0">
                            <input type="radio" class="col-md-1 col-1" id="Disapproval" name="leaveStatus"
                                value="Disapproved" <?= $leave["leaveStatus"] == "Disapproved" ? "checked" : "" ?>>
                            <label class="col-md-7 col-9 text-start" for="Disapproval">Disapproval due to:</label>
                            <textarea class="form-control ms-3" name="disapprovalDetails"><?= htmlspecialchars($leave["disapprovalDetails"] ?? '') ?></textarea>
                        </div>
                        <div
                            class="admin mt-5 pb-5 d-flex flex-row col-md-12 justify-content-center align-items-center gap-5">
                            <div class="hrdo col-md-5">
                                <p style="border-bottom: solid 1px #000" class="m-0"></p>
                                <p class="text-center text-dark">HRDO</p>
                            </div>
                            <div class="admin col-md-5">
                                <p style="border-bottom: solid 1px #000" class="m-0"></p>
                                <p class="text-center text-dark">Administrator</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
// Auto-calculation script
document.addEventListener("DOMContentLoaded", () => {
    const leaveTypes = ["vacation", "sick", "special"];
    const leaveTypeInput = document.querySelector("input[name='leaveType']:checked");
    const numberOfDaysInput = document.querySelector("input[name='numberOfDays']");

    let selectedLeaveType = "";
    if (leaveTypeInput) {
        const val = leaveTypeInput.value.toLowerCase();
        if (val.includes("vacation")) selectedLeaveType = "vacation";
        else if (val.includes("sick")) selectedLeaveType = "sick";
        else if (val.includes("special")) selectedLeaveType = "special";
    }

    leaveTypes.forEach(type => {
        const balance = document.querySelector(`input[name='${type}Balance']`);
        const earned = document.querySelector(`input[name='${type}Earned']`);
        const credits = document.querySelector(`input[name='${type}Credits']`);
        const lessLeave = document.querySelector(`input[name='${type}LessLeave']`);
        const balanceToDate = document.querySelector(`input[name='${type}BalanceToDate']`);

        function updateCalculation() {
            const balVal = parseFloat(balance.value) || 0;
            const earnedVal = parseFloat(earned.value.replace('+', '')) || 0;
            let lessVal = parseFloat(lessLeave.value) || 0;
            const leaveDays = parseFloat(numberOfDaysInput.value) || 0;

            if (type === selectedLeaveType) {
                lessVal = leaveDays;
                lessLeave.value = leaveDays;
            }

            const totalCredits = balVal + earnedVal;
            const finalBalance = totalCredits - lessVal;

            credits.value = totalCredits.toFixed(2);
            balanceToDate.value = finalBalance.toFixed(2);
        }

        [balance, earned, lessLeave, numberOfDaysInput].forEach(input => {
            input?.addEventListener("input", updateCalculation);
        });

        if (balance && earned && credits && lessLeave && balanceToDate) {
            updateCalculation();
        }
    });
});

// Form submission handling
$(document).ready(function() {
    // Handle confirmation modal submit
    $('#confirmSubmitBtn').on('click', function() {
        // Close confirmation modal
        $('#updateModalEBG').modal('hide');

        // Submit the main form
        $('#leaveProcess-form').submit();
    });

    // Main form submission
    $('#leaveProcess-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);

        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $submitBtn = $('#showConfirmationBtn');

        $submitBtn.prop("disabled", true).html(
            '<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=leaveProcess_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                Swal.fire({
                    title: response.status === 1 ? 'Success!' : 'Error!',
                    text: response.message,
                    icon: response.status === 1 ? 'success' : 'error',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#leaveDetailsModal').modal('hide');
                    window.location.href = "index.php?page=contents/leave";
                });
            },
            error: function(jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: 'Connection Error',
                    text: 'Please check your connection and try again.',
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            complete: function() {
                $form.data("isSubmitted", false);
                $submitBtn.prop("disabled", false).html(
                    '<i class="fas fa-check"></i> Submit Leave');
            }


        });
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const printBtn = document.querySelector(".btn.btn-info.m-0");
    if (printBtn) {
        printBtn.addEventListener("click", generatePDF);
    }
});

function generatePDF() {
    const formElement = document.querySelector("#leaveProcess-form");
    if (!formElement) {
        alert("Leave form not found to generate PDF.");
        return;
    }

    const clonedForm = formElement.cloneNode(true);

    // Remove interactive or hidden elements
    clonedForm.querySelectorAll(
        "button, .modal-footer, .modal-header.bg-primary, .btn, [data-bs-toggle], [data-bs-target], input[type='hidden']"
    ).forEach(el => el.remove());

    // Convert checked radio buttons to text
    clonedForm.querySelectorAll('input[type="radio"]').forEach(radio => {
        if (radio.checked) {
            const label = document.createElement('span');
            label.textContent = '✓ ' + (radio.nextElementSibling?.textContent.trim() || radio.value);
            label.style.marginLeft = '5px';
            radio.parentNode.replaceChild(label, radio);
        } else {
            radio.remove();
        }
    });

    // Simplify readonly fields
    clonedForm.querySelectorAll('input[readonly], textarea[readonly]').forEach(input => {
        input.style.border = 'none';
        input.style.background = 'transparent';
    });

    const orgHeader = clonedForm.querySelector("h5.w-100.text-center.m-0");
    const pdfContainer = document.createElement('div');
    pdfContainer.style.padding = '20px';
    pdfContainer.style.fontFamily = 'Arial, sans-serif';

    if (orgHeader) {
        let current = orgHeader;
        while (current) {
            pdfContainer.appendChild(current.cloneNode(true));
            current = current.nextElementSibling;
        }
    } else {
        pdfContainer.innerHTML = clonedForm.innerHTML;
    }

    const options = {
        margin: [0.5, 0.5, 0.5, 0.5],
        filename: `Leave_Request_${document.querySelector('input[name="lastname"]')?.value || 'Form'}.pdf`,
        image: {
            type: 'jpeg',
            quality: 1
        },
        html2canvas: {
            scale: 2,
            useCORS: true
        },
        jsPDF: {
            unit: 'in',
            format: 'a4',
            orientation: 'portrait'
        }
    };

    const styles = `
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 11px !important;
                color: #000;
                margin: 0;
                padding: 0;
            }
            .w-100{
                width: 100%;
                text-align: center;
            }
            h5, strong {
                text-align: center;
                display: flex;
            }
            .form-label {
                font-weight: bold;
                font-size: 11px;
                margin-bottom: 0 !important;
                display: flex;
            }

            .form-control, input, textarea {
                border: none;
                border-bottom: 1px solid #000;
                background: transparent;
                font-size: 11px;
                box-sizing: border-box;
            }
                .name-section{
                    display: flex;
                    flex-direction: row !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

            .modal-body {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: wrap;
                gap: 8px;
            }

            /* Fix Bootstrap columns for PDF (make row layout visible) */
            .row {
                display: flex !important;
                flex-wrap: wrap !important;
                width: 100%;
            }
            .col-md-3, .col-md-4, .col-md-6, .col-md-12 {
                flex: 1;
                min-width: 150px;
                padding: 4px;
            }
            .col-md-3 { display: flex; flex-direction: row !important; flex-basis: 25%; }
            .col-md-4 { display: flex; flex-direction: row !important; flex-basis: 33%; }
            .col-md-6 { display: flex; flex-direction: row !important; flex-basis: 50%; }
            .col-md-12 { display: flex; flex-direction: row !important; flex-basis: 100%; }

            .approvalContent {
                border: 1px solid #000;
                margin-top: 15px;
                padding: 10px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
                font-size: 10.5px;
            }
            th, td {
                border: 1px solid #000;
                padding: 4px;
                text-align: center;
            }

            .text-center { text-align: center; }
            .text-danger { color: #d00; }

            @page { size: A4; margin: 0.5in; }
        </style>
    `;

    const html = `
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8">${styles}</head>
        <body>${pdfContainer.innerHTML}</body>
        </html>
    `;

    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();

    html2pdf().set(options).from(doc.body).save().then(() => {
        document.body.removeChild(iframe);
    });
}
</script>