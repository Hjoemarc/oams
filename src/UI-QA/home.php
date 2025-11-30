<body>
   <section class="">
      <div class="mb-4">
         <div class="mx-2">
            <h4><i class="fa fa-tv mx-2"></i>Employee Dashboard</h4>
            <small class="text-muted">Overviews</small>
         </div>
      </div>

      <!-- Grid Row -->
      <div class="row text-center ">
         <?php 
            $leaveCounts = $stmt = $pdo->prepare("SELECT * FROM  leaveCounts WHERE employee_id = :employee_id");
            $leaveCounts->execute([
               'employee_id' => $employee_id
            ]);
            $getCounts = $stmt->fetch(PDO::FETCH_ASSOC);
            $VacationBalance = $getCounts["VacationBalance"];
            $SickBalance = $getCounts["SickBalance"];
            $SpecialBalance = $getCounts["SpecialBalance"];
            $OthersBalance = $getCounts["OthersBalance"];
         ?>
         <div class="col-md-5 col-5 d-flex flex-column rounded bg-danger p-4 m-2">
            <strong class="text-white fs-2"><?= htmlspecialchars($VacationBalance) ?></strong>
            <span class="text-white">Vacation Leave Balance</span>
         </div>
         <div class="col-md-5 col-5 d-flex flex-column rounded bg-dark p-4 m-2">
            <strong class="text-white fs-2"><?= htmlspecialchars($SickBalance) ?></strong>
            <span class="text-white">Sick Leave Balance</span>
         </div>
         <div class="col-md-5 col-5 d-flex flex-column rounded bg-warning p-4 m-2">
            <strong class="text-dark fs-2"><?= htmlspecialchars($SpecialBalance) ?></strong>
            <span class="text-dark">Special Leave Balance</span>
         </div>
         <div class="col-md-5 col-5 d-flex flex-column rounded bg-info p-4 m-2">
            <strong class="text-white fs-2"><?= htmlspecialchars($OthersBalance) ?></strong>
            <span class="text-white">Others Leave Balance</span>
         </div>
      </div>
   </section>
</body>