<?php
    session_start();
    require_once '../../authentication/config.php';
    $employee_id = $_SESSION['employeeData']['employee_id']
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <div class="bg-gradient-primary h-auto p-3 d-flex  align-items-center justify-content-between" style="background-image: linear-gradient(300deg,#E32126, #FF6F8F,#E32126);
    color: #fff;">
        <div class="col-md-7 d-flex">
            <img src="../../assets/image/system_logo/pueri-logo.png" class="me-2" style="height: auto; width: 50px;">
            <h2 class="m-0 d-flex align-items-center fw-bold">Zamboanga Puericulture Center</h2>
        </div>  
        <div class="col-md-3 d-flex justify-content-end">
            <a href="inactive.php?employee_id=<?= $_SESSION['employeeData']['employee_id'] ?>"><button class="btn btn-info m-0 fw-bold me-3 text-white">Back to Home</button></a>
        </div>
    </div>
        <div class="responsive-table col-md-12 d-flex align-items-start justify-content-center">
        <?php 
            $stmtHIsotry = $pdo->prepare("SELECT job_history.job_from, jobtitles.jobTitle, job_history.job_status, job_history.addAt, job_history.new_salary,
            job_history.current_salary FROM job_history
            LEFT JOIN jobtitles ON job_history.job_to = jobtitles.jobTitles_id
            WHERE job_history.employee_id = '$employee_id'");
            $stmtHIsotry->execute();
            $jobs = $stmtHIsotry->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="col-md-11">
             <table class="table table-responsive table-bordered text-center mt-5">
                <thead>
                    <tr>
                        <th>From Position</th>
                        <th>To Position</th>
                        <th>Change Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($jobs as $job) : ?>
                        <tr>
                            <th><?= htmlspecialchars($job["job_from"]) . ' (' . htmlspecialchars($job["new_salary"]) . ')' ?></th>
                            <th><?= htmlspecialchars($job["jobTitle"]) . ' (' . htmlspecialchars($job["current_salary"]) . ')' ?></th>
                            <th><?= htmlspecialchars($job["job_status"]) ?></th>
                            <th><?= htmlspecialchars($job["addAt"]) ?></th>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>