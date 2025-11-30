<?php
    $stmt = $pdo->prepare("SELECT payslip_data.*, deduction_data.*, earning_data.* FROM payslip_data
                        INNER JOIN deduction_data ON payslip_data.deduction_id = deduction_data.deduction_id
                        INNER JOIN earning_data ON payslip_data.earning_id = earning_data.earning_id
                        WHERE payslip_data.employee_id = '$employee_id'");
    $stmt->execute();
    $payslip_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="mx-2 col-md-7">
            <h4><i class="fa fa-peso-sign"></i>ayslip Management</h4>
        </div>
    </div>
    <div class="responsive-table w-100">
        <?php
            $stmtLEave = $pdo->prepare("SELECT * FROM leavereq WHERE employee_id = '$employee_id'");
            $stmtLEave->execute();
            $leaves = $stmtLEave->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
        ?>
        <strong class="fs-5 mt-5">Payslip History</strong>
        <table class="table table-bordered table-sm w-100 mt-3 text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Earnings</th>
                    <th>Deductions</th>
                    <th>Net Pay</th>
                    <th>Month</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($payslip_data as $payslip) : ?>
                    <tr>
                        <th><?= $count++ ?></th>
                        <th><?= htmlspecialchars($payslip["current_pay"]) ?></th>
                        <th><?= htmlspecialchars($payslip["deduction_current"]) ?></th>
                        <th><?= htmlspecialchars($payslip["payslip_net_pay"]) ?></th>
                        <th><?= htmlspecialchars($payslip["monthly"]) ?></th>
                        <th>
                            <a href="index.php?page=contents/payslip&payslip_id=<?= htmlspecialchars($payslip['payslip_pay_id']) ?>">
                                <button class="btn btn-sm btn-danger">
                                    Review payslip
                                </button>
                            </a>
                        </th>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>