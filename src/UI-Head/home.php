<body class="w-100">
   <section class="col-md-10">
      <div class="mb-4">
         <div class="mx-2">
            <h4><i class="fa fa-tv mx-2"></i>Department Dashboard</h4>
            <small class="text-muted">Department Head Overview</small>
         </div>
      </div>

      <?php
      // Get current user's department info (user is logged in as Department Head)
      // $user_id is already available globally
      
      // Fetch user's department info
      $userDeptStmt = $pdo->prepare("
          SELECT 
              u.user_id,
              u.first_name,
              u.last_name,
              u.user_role,
              d.department_id,
              d.department_name,
              d.department_code
          FROM users u
          LEFT JOIN departments d ON u.department_id = d.department_id
          WHERE u.user_id = ?
          AND u.user_role IN ('head', 'admin')
      ");
      $userDeptStmt->execute([$user_id]);
      $userInfo = $userDeptStmt->fetch(PDO::FETCH_ASSOC);
      
      if (!$userInfo) {
          echo "<div class='alert alert-danger'>Error: User not found or not authorized as department head.</div>";
          return;
      }
      
      $department_id = $userInfo['department_id'];
      $department_name = $userInfo['department_name'];
      $department_code = $userInfo['department_code'];
      
      // Fetch all programs in this department
      $programsStmt = $pdo->prepare("
          SELECT * FROM programs 
          WHERE department_id = ?
          ORDER BY program_name
      ");
      $programsStmt->execute([$department_id]);
      $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
      
      if (empty($programs)) {
          echo "<div class='alert alert-warning'>No programs found for {$department_name} department.</div>";
      }
      
      // Get program IDs
      $program_ids = array_column($programs, 'program_id');
      
      // Fetch active accreditation cycles for department programs
      if (!empty($program_ids)) {
          $program_ids_placeholder = implode(',', array_fill(0, count($program_ids), '?'));
          $cyclesStmt = $pdo->prepare("
              SELECT 
                  ac.*,
                  p.program_name,
                  p.program_code,
                  CASE 
                      WHEN ac.status = 'active' THEN 'Active'
                      WHEN ac.status = 'upcoming' THEN 'Upcoming'
                      WHEN ac.status = 'closed' THEN 'Completed'
                      ELSE ac.status
                  END as status_display,
                  DATEDIFF(ac.deadline, CURDATE()) as days_left,
                  DATE_FORMAT(ac.deadline, '%M %d, %Y') as deadline_formatted,
                  DATE_FORMAT(ac.visit_start, '%M %d, %Y') as visit_start_formatted,
                  DATE_FORMAT(ac.visit_end, '%M %d, %Y') as visit_end_formatted
              FROM accreditation_cycles ac
              LEFT JOIN programs p ON ac.program_id = p.program_id
              WHERE ac.program_id IN ($program_ids_placeholder)
              ORDER BY 
                  CASE ac.status 
                      WHEN 'active' THEN 1
                      WHEN 'upcoming' THEN 2
                      WHEN 'closed' THEN 3
                      ELSE 4
                  END,
                  ac.deadline ASC
          ");
          $cyclesStmt->execute($program_ids);
          $cycles = $cyclesStmt->fetchAll(PDO::FETCH_ASSOC);
      } else {
          $cycles = [];
      }
      
      // Fetch program accreditation history
      if (!empty($program_ids)) {
          $accreditationStmt = $pdo->prepare("
              SELECT 
                  pa.*,
                  p.program_name,
                  p.program_code,
                  al.level_name,
                  al.level_code,
                  al.sort_order,
                  DATE_FORMAT(pa.survey_date, '%Y') as survey_year,
                  DATE_FORMAT(pa.survey_date, '%M %d, %Y') as survey_date_formatted
              FROM program_accreditation pa
              LEFT JOIN programs p ON pa.program_id = p.program_id
              LEFT JOIN accreditation_levels al ON pa.accreditation_level = al.level_name OR pa.accreditation_level = al.level_code
              WHERE pa.program_id IN ($program_ids_placeholder)
              ORDER BY p.program_name, pa.survey_date DESC
          ");
          $accreditationStmt->execute($program_ids);
          $accreditations = $accreditationStmt->fetchAll(PDO::FETCH_ASSOC);
      } else {
          $accreditations = [];
      }
      
      // Organize accreditation history by program
      $programAccreditationHistory = [];
      foreach ($accreditations as $acc) {
          $program_id = $acc['program_id'];
          if (!isset($programAccreditationHistory[$program_id])) {
              $programAccreditationHistory[$program_id] = [];
          }
          $programAccreditationHistory[$program_id][] = $acc;
      }
      
      // Fetch compliance tracking for active cycles
      // Fetch compliance tracking for active cycles
$complianceData = [];
$cycle_ids = array_column($cycles, 'cycle_id');

if (!empty($cycle_ids)) {
    $cycle_ids_placeholder = implode(',', array_fill(0, count($cycle_ids), '?'));
    $complianceStmt = $pdo->prepare("
        SELECT 
            ct.*,
            p.program_name,
            p.program_code,
            ai.indicator_code,
            ai.indicator_description,
            ap.parameter_name,
            ap.parameter_code,
            aa.area_name,
            aa.area_number,
            aa.description as area_description,
            ac.cycle_name,
            ac.academic_year,
            ac.status as cycle_status,
            ct.status as compliance_status,
            ct.compliance_score,
            ct.review_date,
            ct.comments
        FROM compliance_tracking ct
        LEFT JOIN programs p ON ct.program_id = p.program_id
        LEFT JOIN accreditation_indicators ai ON ct.indicator_id = ai.indicator_id
        LEFT JOIN accreditation_parameters ap ON ai.parameter_id = ap.parameter_id
        LEFT JOIN accreditation_areas aa ON ap.area_id = aa.area_id
        LEFT JOIN accreditation_cycles ac ON ct.cycle_id = ac.cycle_id
        WHERE ct.cycle_id IN ($cycle_ids_placeholder)
        ORDER BY aa.area_number, ap.parameter_code, ai.indicator_code
    ");
    $complianceStmt->execute($cycle_ids);
    $complianceData = $complianceStmt->fetchAll(PDO::FETCH_ASSOC);
}
      
      
      // Calculate statistics
      $stats = [];
      
      // Total programs in department
      $stats['total_programs'] = count($programs);
      
      // Count cycles by status
      $stats['active_cycles'] = 0;
      $stats['upcoming_cycles'] = 0;
      $stats['completed_cycles'] = 0;
      
      foreach ($cycles as $cycle) {
          switch ($cycle['status']) {
              case 'active':
                  $stats['active_cycles']++;
                  break;
              case 'upcoming':
                  $stats['upcoming_cycles']++;
                  break;
              case 'closed':
                  $stats['completed_cycles']++;
                  break;
          }
      }
      
      // Calculate overall compliance rate
      if (!empty($complianceData)) {
          $totalIndicators = count($complianceData);
          $compliantCount = 0;
          $partialCount = 0;
          $nonCompliantCount = 0;
          $notAssessedCount = 0;
          
          foreach ($complianceData as $record) {
              switch ($record['status']) {
                  case 'compliant': $compliantCount++; break;
                  case 'partial': $partialCount++; break;
                  case 'non_compliant': $nonCompliantCount++; break;
                  case 'not_assessed': $notAssessedCount++; break;
              }
          }
          
          // Weighted calculation: compliant=100%, partial=50%, non_compliant=0%, not_assessed=0%
          $totalScore = ($compliantCount * 100) + ($partialCount * 50);
          $maxPossibleScore = ($totalIndicators - $notAssessedCount) * 100;
          
          if ($maxPossibleScore > 0) {
              $stats['compliance_rate'] = round(($totalScore / $maxPossibleScore) * 100, 1);
          } else {
              $stats['compliance_rate'] = 0;
          }
          
          $stats['compliant_indicators'] = $compliantCount;
          $stats['partial_indicators'] = $partialCount;
          $stats['non_compliant_indicators'] = $nonCompliantCount;
          $stats['total_assessed'] = $totalIndicators - $notAssessedCount;
      } else {
          $stats['compliance_rate'] = 0;
          $stats['compliant_indicators'] = 0;
          $stats['partial_indicators'] = 0;
          $stats['non_compliant_indicators'] = 0;
          $stats['total_assessed'] = 0;
      }
      
      // Fetch document status for department programs
      if (!empty($program_ids)) {
          $documentsStmt = $pdo->prepare("
              SELECT 
                  d.status,
                  COUNT(*) as count,
                  p.program_name
              FROM documents d
              LEFT JOIN programs p ON d.program_id = p.program_id
              WHERE d.program_id IN ($program_ids_placeholder)
              GROUP BY d.status, p.program_name
              ORDER BY d.status
          ");
          $documentsStmt->execute($program_ids);
          $documentStatus = $documentsStmt->fetchAll(PDO::FETCH_ASSOC);
      } else {
          $documentStatus = [];
      }
      
      // Calculate document statistics
      $stats['total_documents'] = 0;
      $stats['approved_documents'] = 0;
      $stats['pending_documents'] = 0;
      
      foreach ($documentStatus as $doc) {
          $stats['total_documents'] += $doc['count'];
          if ($doc['status'] == 'approved') {
              $stats['approved_documents'] += $doc['count'];
          } elseif (in_array($doc['status'], ['draft', 'submitted', 'under_review', 'needs_revision'])) {
              $stats['pending_documents'] += $doc['count'];
          }
      }
      
      // Fetch assignment status for department programs
      if (!empty($program_ids)) {
          $assignmentsStmt = $pdo->prepare("
              SELECT 
                  a.status,
                  COUNT(*) as count,
                  p.program_name,
                  u.first_name,
                  u.last_name,
                  ai.indicator_code
              FROM assignments a
              LEFT JOIN programs p ON a.program_id = p.program_id
              LEFT JOIN users u ON a.user_id = u.user_id
              LEFT JOIN accreditation_indicators ai ON a.indicator_id = ai.indicator_id
              WHERE a.program_id IN ($program_ids_placeholder)
              GROUP BY a.status, p.program_name
              ORDER BY p.program_name, a.status
          ");
          $assignmentsStmt->execute($program_ids);
          $assignmentsData = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);
      } else {
          $assignmentsData = [];
      }
      
      // Calculate deadlines approaching (within 30 days)
      if (!empty($program_ids)) {
          $deadlineStmt = $pdo->prepare("
              SELECT 
                  ac.*,
                  p.program_name,
                  p.program_code,
                  DATEDIFF(ac.deadline, CURDATE()) as days_left,
                  DATE_FORMAT(ac.deadline, '%M %d, %Y') as deadline_formatted
              FROM accreditation_cycles ac
              LEFT JOIN programs p ON ac.program_id = p.program_id
              WHERE ac.program_id IN ($program_ids_placeholder)
              AND ac.deadline IS NOT NULL
              AND ac.deadline >= CURDATE()
              AND ac.deadline <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              AND ac.status IN ('active', 'upcoming')
              ORDER BY ac.deadline ASC
              LIMIT 5
          ");
          $deadlineStmt->execute($program_ids);
          $approachingDeadlines = $deadlineStmt->fetchAll(PDO::FETCH_ASSOC);
      } else {
          $approachingDeadlines = [];
      }
      
      // Get all accreditation levels for tracking
      $levelsStmt = $pdo->query("SELECT * FROM accreditation_levels ORDER BY sort_order");
      $allLevels = $levelsStmt->fetchAll(PDO::FETCH_ASSOC);
      
      // Calculate program level progress
      $programLevels = [];
      foreach ($programs as $program) {
          $program_id = $program['program_id'];
          $programLevels[$program_id] = [
              'program' => $program,
              'current_level' => 'Not Accredited',
              'next_level' => null,
              'level_progress' => 0
          ];
          
          // Find highest accreditation level for this program
          $highestAcc = null;
          foreach ($accreditations as $acc) {
              if ($acc['program_id'] == $program_id) {
                  if (!$highestAcc || ($acc['sort_order'] > $highestAcc['sort_order'])) {
                      $highestAcc = $acc;
                  }
              }
          }
          
          if ($highestAcc) {
              $programLevels[$program_id]['current_level'] = $highestAcc['level_name'] ?? $highestAcc['accreditation_level'];
              
              // Find next level
              foreach ($allLevels as $level) {
                  if ($level['sort_order'] > ($highestAcc['sort_order'] ?? 0)) {
                      $programLevels[$program_id]['next_level'] = $level['level_name'];
                      break;
                  }
              }
          }
          
          // Calculate progress for this program (based on compliance for active cycles)
          $programCompliance = array_filter($complianceData, function($c) use ($program_id) {
              return $c['program_id'] == $program_id;
          });
          
          if (!empty($programCompliance)) {
              $totalProgramIndicators = count($programCompliance);
              $programCompliantCount = 0;
              $programPartialCount = 0;
              
              foreach ($programCompliance as $record) {
                  if ($record['status'] == 'compliant') $programCompliantCount++;
                  if ($record['status'] == 'partial') $programPartialCount++;
              }
              
              $programTotalScore = ($programCompliantCount * 100) + ($programPartialCount * 50);
              $programMaxScore = $totalProgramIndicators * 100;
              
              if ($programMaxScore > 0) {
                  $programLevels[$program_id]['level_progress'] = round(($programTotalScore / $programMaxScore) * 100, 1);
              }
          }
      }
      ?>

      <!-- Welcome Header -->
      <div class="row mb-4">
         <div class="col-12">
            <div class="card shadow">
               <div class="card-body">
                  <h5 class="card-title">Welcome, <?php echo htmlspecialchars($userInfo['first_name'] . ' ' . $userInfo['last_name']); ?></h5>
                  <p class="card-text text-dark">
                     <i class="fa fa-building text-dark"></i> 
                     <strong><?php echo htmlspecialchars($department_name); ?> (<?php echo htmlspecialchars($department_code); ?>)</strong> 
                     | Department Head Dashboard
                  </p>
               </div>
            </div>
         </div>
      </div>

      <!-- Department Statistics -->
      <div class="row mb-4">
         <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
               <div class="card-body">
                  <div class="row no-gutters align-items-center">
                     <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                           Department Programs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_programs']; ?></div>
                     </div>
                     <div class="col-auto">
                        <i class="fa fa-graduation-cap fa-2x text-gray-300"></i>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
               <div class="card-body">
                  <div class="row no-gutters align-items-center">
                     <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                           Active Accreditation</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_cycles']; ?></div>
                     </div>
                     <div class="col-auto">
                        <i class="fa fa-sync-alt fa-2x text-gray-300"></i>
                     </div>
                  </div>
                  <div class="mt-2">
                     <small class="text-muted">
                        <?php echo $stats['upcoming_cycles']; ?> upcoming • <?php echo $stats['completed_cycles']; ?> completed
                     </small>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
               <div class="card-body">
                  <div class="row no-gutters align-items-center">
                     <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                           Compliance Rate</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['compliance_rate']; ?>%</div>
                     </div>
                     <div class="col-auto">
                        <i class="fa fa-chart-line fa-2x text-gray-300"></i>
                     </div>
                  </div>
                  <div class="mt-2">
                     <small class="text-muted">
                        <?php echo $stats['compliant_indicators']; ?> compliant • <?php echo $stats['partial_indicators']; ?> partial
                     </small>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
               <div class="card-body">
                  <div class="row no-gutters align-items-center">
                     <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                           Documents Status</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['approved_documents']; ?> / <?php echo $stats['total_documents']; ?></div>
                     </div>
                     <div class="col-auto">
                        <i class="fa fa-file-alt fa-2x text-gray-300"></i>
                     </div>
                  </div>
                  <div class="mt-2">
                     <small class="text-muted">
                        <?php echo $stats['pending_documents']; ?> pending review
                     </small>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Program Accreditation Status -->
      <div class="row mb-4">
         <div class="col-md-12">
            <div class="card shadow mb-4">
               <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-dark">
                     <i class="fa fa-graduation-cap mr-2"></i>Program Accreditation Status
                  </h6>
               </div>
               <div class="card-body">
                  <div class="table-responsive">
                     <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                           <tr>
                              <th>Program</th>
                              <th>Current Level</th>
                              <th>Next Target</th>
                              <th>Current Progress</th>
                              <th>Active Cycle</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php foreach($programLevels as $programLevel): 
                              $program = $programLevel['program'];
                              $progress = $programLevel['level_progress'];
                              $progressColor = $progress >= 80 ? 'dark' : ($progress >= 50 ? 'dark' : 'danger');
                              
                              // Find active cycle for this program
                              $activeCycle = null;
                              foreach ($cycles as $cycle) {
                                  if ($cycle['program_id'] == $program['program_id'] && $cycle['status'] == 'active') {
                                      $activeCycle = $cycle;
                                      break;
                                  }
                              }
                           ?>
                           <tr>
                              <td>
                                 <strong><?php echo htmlspecialchars($program['program_code']); ?></strong><br>
                                 <small class="text-dark"><?php echo htmlspecialchars($program['program_name']); ?></small>
                              </td>
                              <td>
                                 <span class="badge text-dark badge-text"><?php echo htmlspecialchars($programLevel['current_level']); ?></span>
                              </td>
                              <td>
                                 <?php if ($programLevel['next_level']): ?>
                                 <span class="badge text-dark badge-dark"><?php echo htmlspecialchars($programLevel['next_level']); ?></span>
                                 <?php else: ?>
                                 <span class="text-dark">Highest Level</span>
                                 <?php endif; ?>
                              </td>
                              <td>
                                 <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 20px;">
                                       <div class="progress-bar text-dark bg-<?php echo $progressColor; ?>" 
                                            role="progressbar" 
                                            style="width: <?php echo $progress; ?>%"
                                            aria-valuenow="<?php echo $progress; ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                       </div>
                                    </div>
                                    <span class="ml-2 font-weight-bold"><?php echo $progress; ?>%</span>
                                 </div>
                              </td>
                              <td>
                                 <?php if ($activeCycle): ?>
                                 <span class="badge text-dark badge-dark">Active</span><br>
                                 <small class="text-dark"><?php echo htmlspecialchars($activeCycle['cycle_name']); ?></small>
                                 <?php else: ?>
                                 <span class="badge text-dark badge-dark">No Active Cycle</span>
                                 <?php endif; ?>
                              </td>
                           </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>


      <!-- Accreditation History -->
      <?php if (!empty($accreditations)): ?>
      <div class="row mb-4">
         <div class="col-md-12">
            <div class="card shadow mb-4">
               <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-dark">
                     <i class="fa fa-history mr-2"></i>Accreditation History
                  </h6>
               </div>
               <div class="card-body">
                  <div class="timeline">
                     <?php 
                     // Sort accreditations by date
                     usort($accreditations, function($a, $b) {
                         return strtotime($b['survey_date']) - strtotime($a['survey_date']);
                     });
                     
                     $currentYear = null;
                     foreach ($accreditations as $acc): 
                        if ($currentYear != $acc['survey_year']):
                           $currentYear = $acc['survey_year'];
                     ?>
                     <div class="timeline-year">
                        <h5><?php echo $currentYear; ?></h5>
                     </div>
                     <?php endif; ?>
                     <div class="timeline-item">
                        <div class="timeline-marker">
                           <i class="fa fa-award text-success"></i>
                        </div>
                        <div class="timeline-content">
                           <h6>
                              <strong><?php echo htmlspecialchars($acc['program_name']); ?></strong>
                              achieved
                              <span class="badge badge-info"><?php echo htmlspecialchars($acc['level_name'] ?? $acc['accreditation_level']); ?></span>
                           </h6>
                           <p class="text-muted mb-1">
                              <i class="fa fa-calendar"></i> 
                              <?php echo $acc['survey_date_formatted']; ?> 
                              • 
                              <i class="fa fa-university"></i> 
                              <?php echo htmlspecialchars($acc['accrediting_body']); ?>
                           </p>
                           <?php if ($acc['remarks']): ?>
                           <p class="mb-0"><?php echo htmlspecialchars(substr($acc['remarks'], 0, 100)); ?>...</p>
                           <?php endif; ?>
                        </div>
                     </div>
                     <?php endforeach; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php endif; ?>

   </section>

   <style>
      .timeline {
         position: relative;
         padding: 20px 0;
      }
      
      .timeline:before {
         content: '';
         position: absolute;
         left: 50px;
         top: 0;
         bottom: 0;
         width: 2px;
         background: #e0e0e0;
      }
      
      .timeline-year {
         margin-left: 30px;
         margin-bottom: 20px;
         padding: 5px 15px;
         background: #f8f9fa;
         border-radius: 20px;
         display: inline-block;
      }
      
      .timeline-item {
         display: flex;
         margin-bottom: 20px;
      }
      
      .timeline-marker {
         width: 50px;
         text-align: center;
         z-index: 1;
      }
      
      .timeline-marker i {
         background: white;
         padding: 10px;
         border-radius: 50%;
         font-size: 18px;
      }
      
      .timeline-content {
         flex: 1;
         padding: 10px 15px;
         background: white;
         border-radius: 5px;
         box-shadow: 0 1px 3px rgba(0,0,0,0.1);
         margin-left: 10px;
      }
      
      .progress {
         height: 10px;
      }
   </style>

   <script>
      // Action functions
      function viewProgramDetails(programId) {
         window.location.href = 'program_details.php?id=' + programId;
      }
      
      function viewAccreditationHistory(programId) {
         window.location.href = 'accreditation_history.php?program_id=' + programId;
      }
      
      function viewCycleDetails(cycleId) {
         window.location.href = 'cycle_details.php?id=' + cycleId;
      }
      
      function viewComplianceReport(cycleId) {
         window.location.href = 'compliance_report.php?cycle_id=' + cycleId;
      }
      
      function viewAllCycles() {
         window.location.href = 'accreditation_cycles.php';
      }
      
      function createNewCycle() {
         window.location.href = 'create_cycle.php';
      }
      
      function generateReport() {
         window.location.href = 'generate_report.php';
      }
      
      function viewComplianceDashboard() {
         window.location.href = 'compliance_dashboard.php';
      }
      
      function manageAssignments() {
         window.location.href = 'assignments.php';
      }
   </script>
</body>