<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="mx-2">
            <h4><i class="fa fa-clock mx-1 "></i>Employment History</h4>
        </div>
    </div>
    <div class="responsive-table">
        <?php 
            $stmtHIsotry = $pdo->prepare("SELECT job_history.job_from, jobtitles.jobTitle, job_history.job_status, job_history.addAt, job_history.new_salary,
            job_history.current_salary FROM job_history
            LEFT JOIN jobtitles ON job_history.job_to = jobtitles.jobTitles_id
            WHERE job_history.employee_id = '$employee_id'");
            $stmtHIsotry->execute();
            $jobs = $stmtHIsotry->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table class="table table-responsive table-bordered text-center">
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
</section>