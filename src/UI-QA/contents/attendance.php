<section>
    <div class="d-flex justify-content-between align-items-center mb-3 col-md-12">
        <div class="mx-2 col-md-6">
            <h4><i class="fa fa-clock mx-1 "></i>Attendance Time In and Time Out</h4>
            <small class="text-muted">View the employee profiles, attendance, and payroll</small>
        </div>
        <div class="col-md-6 d-flex flex-row justify-content-end gap-2">
            <?php 
                $stmt = $pdo->prepare("SELECT date, time_in, time_out, attendance_id FROM attendance WHERE date = CURDATE() AND employee_id = '$employee_id'");
                $stmt->execute();
                $dateNow = $stmt->fetch(PDO::FETCH_ASSOC);
                $time_in_date = $dateNow["time_in"] ?? '';
                $time_out_date = $dateNow["time_out"] ?? '';
                if($time_in_date == null){
            ?>
            <form id="attendance-form-time_in">
                <input type="hidden" value="<?= htmlspecialchars($employee_id) ?>" name="employee_id">
                <button class="btn btn-danger">TIME-IN</button>
            </form>
            <?php }else{ ?>
                <button class="btn btn-danger" disabled>TIME-IN</button>
            <?php }
            if($time_out_date == null) {?>
            <form id="attendance-form-time_out">
                <input type="hidden" value="<?= htmlspecialchars($dateNow["attendance_id"]) ?>" name="attendance_id">
                <input type="hidden" value="<?= htmlspecialchars($employee_id) ?>" name="employee_id">
                <button class="btn btn-dark">TIME-OUT</button>
            </form>
            <?php }else{ ?>
                <button class="btn btn-dark" disabled>TIME-OUT</button>
            <?php } ?>
        </div>
    </div>
    <div class="responsive-table w-100">
        <table class="table-responsive table-bordered table-sm text-center w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>TIME IN</th>
                    <th>TIME OUT</th>
                    <th>DATE</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = '$employee_id'");
                    $stmt->execute();
                    $employee_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $countAttendance = 1;
                    foreach($employee_attendance as $attendace) : ?>
                    <tr>
                        <th><?= $countAttendance++ ?></th>
                        <th><?= htmlspecialchars($attendace["time_in"]) ?></th>
                        <th><?= htmlspecialchars($attendace["time_out"]) ?></th>
                        <th><?= htmlspecialchars($attendace["date"]) ?></th>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>