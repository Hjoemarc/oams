<?php
    $payslip_id = $_GET["payslip_id"];
    $stmt = $pdo->prepare("SELECT payslip_data.*, deduction_data.*, earning_data.*, employee_data.*, 
                        employee_data.created_date AS date_of_joining, payslip_data.created_date AS pay_period, 
                        departments.Department_name, jobTitles.jobTitle FROM payslip_data
                        INNER JOIN deduction_data ON payslip_data.deduction_id = deduction_data.deduction_id
                        INNER JOIN earning_data ON payslip_data.earning_id = earning_data.earning_id
                        INNER JOIN employee_data ON payslip_data.employee_id = employee_data.employee_id
                        INNER JOIN hr_data ON employee_data.employee_id = hr_data.employee_id
                        INNER JOIN departments ON hr_data.Department_id = departments.Department_id
                        INNER JOIN jobTitles ON hr_data.jobtitle_id = jobTitles.jobTitles_id
                        WHERE payslip_data.payslip_pay_id = '$payslip_id'");
    $stmt->execute();
    $payslip_data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<main>
    <div class="col-md-11">
        <button class="btn btn-danger btn-sm">Generate Payslip</button>
    </div>
    <div class="col-md-12 d-flex flex-column justify-content-center align-items-center">
        <div class="col-md-12 text-center"><strong>ZAMBOANGA PUERICULTURE CENTER</strong></div>
        <div class="col-md-12 text-center"><span>ORGANIZATION NO. 144 INC.</span></div>
        <div class="col-md-12 text-center"><span>ZAMBOANGA CITY</span></div>
        <div class="col-md-12 text-center mt-1"><span style="border-bottom: solid 1px #000;">P A Y S L I P</span></div>
        <!-- Employee and payslips details -->
        <div class="col-md-8 d-flex justify-content-evenly flex-column mt-4">
            <span>NAME - <span style="border-bottom: solid 1px #000;"><?= htmlspecialchars($payslip_data["firstname"]) . " " . htmlspecialchars($payslip_data["lastname"]) ?></span></span>
            <span>DESIGNATION - <span style="border-bottom: solid 1px #000;"><?= htmlspecialchars($payslip_data["jobTitle"]) ?></span></span>
            <span>PAY PERIOD - <span style="border-bottom: solid 1px #000;"><?= htmlspecialchars($payslip_data["pay_period"]) ?></span></span>
        </div>
        <!-- Table of payslips -->
        <div class="col-md-8 mt-3">
            <div class="col-md-12 d-flex">
                <strong class="col-md-6 text-start">Basic Salary</strong>
                <strong class="col-md-6">P</strong>
            </div>
            <div class="col-md-10 d-flex justify-content-between">      
                <div class="col-md-4 text-end d-flex flex-column">   
                    <div class="col-md-12"><span>HOLIDAY PAY</span></div>
                    <div class="col-md-12"><span>NIGHT DIFFERENTIAL</span></div>
                    <div class="col-md-12"><span>OVERTIME PAY</span></div>
                    <div class="col-md-12"><span>ADJUSTMENT/REFUND</span></div>
                </div>
                <div class="col-md-4 text-end d-flex flex-column">   
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"style="border-bottom: solid 1px #000;"><span>-</span></div>
                    <div class="col-md-12 text-start"style="border-bottom: solid 1px #000;"><span>P</span></div>
                </div>
            </div>
            <div class="col-md-12 d-flex mt-3">
                <strong class="col-md-6 text-start">LESS :</strong>
                <strong class="col-md-6">DEDUCTIONS :</strong>
            </div>
            <div class="col-md-10 d-flex justify-content-between">      
                <div class="col-md-4 text-end d-flex flex-column">   
                    <div class="col-md-12"><span>PAG-IBIG MPL LOAN</span></div>
                    <div class="col-md-12"><span>PAG-IBIG CALAMITY LOAN</span></div>
                    <div class="col-md-12"><span>PROVIDENT LOAN</span></div>
                    <div class="col-md-12"><span>SSS LOAN</span></div>
                    <div class="col-md-12"><span>SSS CALAMITY</span></div>
                    <div class="col-md-12"><span>TUITION FEE</span></div>
                    <div class="col-md-12"><span>PHARMACY</span></div>
                    <div class="col-md-12"><span>previous</span></div>
                    <div class="col-md-12"><span>OTHER ACCOUNT/OVERDRAFT</span></div>
                    <div class="col-md-12"><span>LEAVE W/0 PAY/TARDINESS</span></div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                    <div class="col-md-12"><span>-</span></div>
                </div>
                <div class="col-md-4 text-start d-flex flex-column">   
                    
                </div>
            </div>
            <div class="col-md-10 d-flex mt-3">
                <strong class="col-md-8 text-start">TOTAL DEDUCTIONS :</strong>
                <strong class="col-md-4 text-end"style="border-bottom: solid 1px #000;">-</strong>
            </div>
            <div class="col-md-10 d-flex ">
                <strong class="col-md-8 text-start">NET PAY FOR 30TH :</strong>
                <strong class="col-md-4 text-start"style="border-bottom: solid 1px #000;">P</strong>
            </div>
        </div>
        <!-- footer extra contents -->
         <div class="col-md-8 text-start mt-5"><strong>Prepared by:</strong></div>
         <div class="col-md-8 text-start"><strong class="ms-2">Julius Bagio</strong></div>
         <div class="col-md-8 text-start"><strong class="ms-2">HR Staff</strong></div>
    </div>
</main>