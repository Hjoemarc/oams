<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4><i class="fas fa-calendar-check"></i> HR Settings</h4>
            <small class="text-muted">Create Template for schedules</small>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSchedules">
                <i class="fas fa-plus me-1"></i> add schedules
            </button>
        </div>
    </div>
    <!-- ADD SCHEDULES MODAL -->
    <div class="modal fade" id="createSchedules" tabindex="-1" aria-labelledby="createSchedulesLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="createSchedulesLabel">Create New Employee Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="schedule-template-form" method="post">
                        <div class="mx-2">
                            <label class="form-label">Schedule name</label>
                            <input required type="text" name="scheduleName" class="form-control">
                        </div>
                        <div class="mx-2">
                            <label class="form-label">Schedule From</label>
                            <input required type="time" name="schedule_from" class="form-control">
                        </div>
                        <div class="mx-2">
                            <label class="form-label">Schedule to</label>
                            <input required type="time" name="schedule_to" class="form-control">
                        </div>
                        <div class="mx-2">
                            <label class="form-label">Work Shift</label>
                            <select required name="shift" id="" class="form-select">
                                <option value="">Select Shift</option>
                                <option value="day">Day Shift</option>
                                <option value="night">Night Shift</option>
                            </select>
                        </div>
                        <div class="mx-2">
                            <label class="form-label">Work Days</label>
                            <input type="text" name="day" class="form-control" placeholder="ex. M-W-F">
                        </div>
                        <div class="mx-2">
                            <label class="form-label">Department</label>
                            <select required name="department" id="" class="form-select">
                                <option value="">Select Department</option>
                                <option value="SCHOOL">SCHOOL</option>
                                <option value="HOSPITAL">HOSPITAL</option>
                                <option value="ADMIN">ADMIN</option>
                                <option value="HR">HR</option>
                            </select>
                        </div>
                        <!-- Form Submission -->
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-person-plus-fill me-1"></i> Create Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body col-md-12 col-12 d-flex justify-content-between flex-wrap">
            <ul class="nav nav-tabs col-md-6 col-12" id="SchedulesTabs" role="tablist">
                <li class="nav-item col-md-3" role="presentation">
                    <button class="nav-link active w-100 h-100" id="approved-tab" data-bs-toggle="tab"
                        data-bs-target="#school" type="button" role="tab" aria-controls="school" aria-selected="true">
                        <i class="fa-solid fa-user-tie me-2"></i>School
                    </button>
                </li>
                <li class="nav-item col-md-3" role="presentation">
                    <button class="nav-link w-100 h-100" id="pending-tab" data-bs-toggle="tab" data-bs-target="#hr"
                        type="button" role="tab" aria-controls="hr" aria-selected="false">
                        <i class="fa-solid fa-user-plus me-2"></i>HR
                    </button>
                </li>
                <li class="nav-item col-md-3" role="presentation">
                    <button class="nav-link w-100 h-100" id="rejected-tab" data-bs-toggle="tab"
                        data-bs-target="#hospital" type="button" role="tab" aria-controls="hospital"
                        aria-selected="false">
                        <i class="fa-solid fa-user-minus me-2"></i>Hospital
                    </button>
                </li>
                <li class="nav-item col-md-3" role="presentation">
                    <button class="nav-link w-100 h-100" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#admin"
                        type="button" role="tab" aria-controls="admin" aria-selected="false">
                        <i class="fa-solid fa-user-minus me-2"></i>Admin
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body pt-0">
            <div class="tab-content" id="employeesTabContent">
                <!-- Approved Employees -->
                <div class="tab-pane fade show active" id="school" role="tabpanel" aria-labelledby="approved-tab"
                    tabindex="0">
                    <div class="responsive-table w-100">
                        <table class="table table-body table-responsive table-bordered table-sm w-100 text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Schedule Type</th>
                                    <th>From</th>
                                    <th>TO</th>
                                    <th>Shift</th>
                                    <th>Days</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stmt = $pdo->prepare("SELECT * FROM sched_template WHERE department = 'SCHOOL'");
                                $stmt->execute();
                                $SchoolSched = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $count = 1;
                                foreach($SchoolSched as $school) : 
                                ?>
                                <tr>
                                    <th><?= $count++ ?></th>
                                    <th><?= htmlspecialchars($school["scheduleName"]) ?></th>
                                    <th><?= htmlspecialchars($school["schedule_from"]) ?></th>
                                    <th><?= htmlspecialchars($school["schedule_to"]) ?></th>
                                    <th><?= htmlspecialchars($school["shift"]) ?></th>
                                    <th><?= htmlspecialchars($school["day"]) ?></th>
                                    <th>
                                        <button class="btn btn-sm m-0 btn-info editScheduleBtn" data-bs-toggle="modal"
                                            data-bs-target="#editSchedule"
                                            data-id="<?= htmlspecialchars($school["template_id"]) ?>">
                                            EDIT
                                        </button>
                                        <button class="btn btn-sm m-0 btn-danger" id="scheduleTemplateId"
                                            data-bs-toggle="modal" data-bs-target="#scheduleDelete"
                                            data-id="<?= htmlspecialchars($school["template_id"]) ?>">DELETE</button>
                                    </th>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- delete modal -->
                <div class="modal fade" id="scheduleDelete" tabindex="-1" aria-labelledby="scheduleDeleteLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form class="modal-content" id="delete-template-form">
                            <div class="modal-header bg-gradient-primary text-white">
                                <h5 class="modal-title text-white" id="scheduleDeleteLabel">Dleete confirmation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete this template?
                                <input type="hidden" name="TemplateId" id="TemplateId">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- edit modal -->
                <div class="modal fade" id="editSchedule" tabindex="-1" aria-labelledby="editScheduleLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="edit-schedule">
                            <div class="modal-content">
                                <div class="modal-header bg-gradient-primary text-white">
                                    <h5 class="modal-title text-white" id="editScheduleLabel">Edit Schdule Inforation
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <input type="hidden" name="template_id" id="template_id">

                                    <div class="mx-2">
                                        <label class="form-label">Schedule name</label>
                                        <input required type="text" name="scheduleName" class="form-control">
                                    </div>
                                    <div class="mx-2">
                                        <label class="form-label">Schedule From</label>
                                        <input required type="time" name="schedule_from" class="form-control">
                                    </div>
                                    <div class="mx-2">
                                        <label class="form-label">Schedule to</label>
                                        <input required type="time" name="schedule_to" class="form-control">
                                    </div>
                                    <div class="mx-2">
                                        <label class="form-label">Work Shift</label>
                                        <select required name="shift" id="" class="form-select">
                                            <option value="">Select Shift</option>
                                            <option value="day">Day Shift</option>
                                            <option value="night">Night Shift</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label class="form-label">Work Days</label>
                                        <input type="text" name="day" class="form-control" placeholder="ex. M-W-F">
                                    </div>
                                    <div class="mx-2">
                                        <label class="form-label">Department</label>
                                        <select required name="department" id="" class="form-select">
                                            <option value="">Select Department</option>
                                            <option value="SCHOOL">SCHOOL</option>
                                            <option value="HOSPITAL">HOSPITAL</option>
                                            <option value="ADMIN">ADMIN</option>
                                            <option value="HR">HR</option>
                                        </select>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger">Update Schedule</button>
                                        <button type="button" class="btn btn-dark"
                                            data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade show" id="hr" role="tabpanel" aria-labelledby="approved-tab" tabindex="0">
                    <div class="responsive-table w-100">
                        <table class="table table-body table-responsive table-bordered table-sm w-100 text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Schedule Type</th>
                                    <th>From</th>
                                    <th>TO</th>
                                    <th>Shift</th>
                                    <th>Days</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stmt = $pdo->prepare("SELECT * FROM sched_template WHERE department = 'HR'");
                                $stmt->execute();
                                $SchoolSched = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $count = 1;
                                foreach($SchoolSched as $school) : 
                                ?>
                                <tr>
                                    <th><?= $count++ ?></th>
                                    <th><?= htmlspecialchars($school["scheduleName"]) ?></th>
                                    <th><?= htmlspecialchars($school["schedule_from"]) ?></th>
                                    <th><?= htmlspecialchars($school["schedule_to"]) ?></th>
                                    <th><?= htmlspecialchars($school["shift"]) ?></th>
                                    <th><?= htmlspecialchars($school["day"]) ?></th>
                                    <th>
                                        <button class="btn btn-sm m-0 btn-info editScheduleBtn" data-bs-toggle="modal"
                                            data-bs-target="#editSchedule"
                                            data-id="<?= htmlspecialchars($school["template_id"]) ?>">
                                            EDIT
                                        </button>
                                        <button class="btn btn-sm m-0 btn-danger" id="scheduleTemplateId"
                                            data-bs-toggle="modal" data-bs-target="#scheduleDelete"
                                            data-id="<?= htmlspecialchars($school["template_id"]) ?>">DELETE</button>
                                    </th>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade show" id="hospital" role="tabpanel" aria-labelledby="approved-tab"
                    tabindex="0">
                    <div class="responsive-table w-100">
                        <table class="table table-body table-responsive table-bordered table-sm w-100 text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Schedule Type</th>
                                    <th>From</th>
                                    <th>TO</th>
                                    <th>Shift</th>
                                    <th>Days</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stmt = $pdo->prepare("SELECT * FROM sched_template WHERE department = 'HOSPITAL'");
                                $stmt->execute();
                                $SchoolSched = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $count = 1;
                                foreach($SchoolSched as $school) : 
                                ?>
                                <tr>
                                    <th><?= $count++ ?></th>
                                    <th><?= htmlspecialchars($school["scheduleName"]) ?></th>
                                    <th><?= htmlspecialchars($school["schedule_from"]) ?></th>
                                    <th><?= htmlspecialchars($school["schedule_to"]) ?></th>
                                    <th><?= htmlspecialchars($school["shift"]) ?></th>
                                    <th><?= htmlspecialchars($school["day"]) ?></th>
                                    <th>
                                        <button class="btn btn-sm m-0 btn-info editScheduleBtn" data-bs-toggle="modal"
                                            data-bs-target="#editSchedule"
                                            data-id="<?= htmlspecialchars($school["template_id"]) ?>">
                                            EDIT
                                        </button>
                                        <button class="btn btn-sm m-0 btn-danger" id="scheduleTemplateId"
                                            data-bs-toggle="modal" data-bs-target="#scheduleDelete"
                                            data-id="<?= htmlspecialchars($school["template_id"]) ?>">DELETE</button>
                                    </th>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade show" id="admin" role="tabpanel" aria-labelledby="approved-tab" tabindex="0">
                    <div class="responsive-table w-100">
                        <table class="table table-body table-responsive table-bordered table-sm w-100 text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Schedule Type</th>
                                    <th>From</th>
                                    <th>TO</th>
                                    <th>Shift</th>
                                    <th>Days</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stmt = $pdo->prepare("SELECT * FROM sched_template WHERE department = 'ADMIN'");
                                $stmt->execute();
                                $SchoolSched = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $count = 1;
                                foreach($SchoolSched as $school) : 
                                ?>
                                <tr>
                                    <th><?= $count++ ?></th>
                                    <th><?= htmlspecialchars($school["scheduleName"]) ?></th>
                                    <th><?= htmlspecialchars($school["schedule_from"]) ?></th>
                                    <th><?= htmlspecialchars($school["schedule_to"]) ?></th>
                                    <th><?= htmlspecialchars($school["shift"]) ?></th>
                                    <th><?= htmlspecialchars($school["day"]) ?></th>
                                    <th>
                                        <button class="btn btn-sm m-0 btn-info editScheduleBtn" data-bs-toggle="modal"
                                            data-bs-target="#editSchedule"
                                            data-id="<?= htmlspecialchars($school["template_id"]) ?>">
                                            EDIT
                                        </button>
                                        <button class="btn btn-sm m-0 btn-danger" id="scheduleTemplateId"
                                            data-bs-toggle="modal" data-bs-target="#scheduleDelete"
                                            data-id="<?= htmlspecialchars($school["template_id"]) ?>">DELETE</button>
                                    </th>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    SchedulesTab();
});

function SchedulesTab() {
    const tabButtons = document.querySelectorAll('#SchedulesTabs .nav-link');
    const tabContents = document.querySelectorAll('.tab-pane');

    // Initialize tabs - hide all except active
    tabContents.forEach(content => {
        if (!content.classList.contains('active')) {
            content.style.display = 'none';
        }
    });

    tabButtons.forEach((button) => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Update active button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Hide all tab contents
            tabContents.forEach(content => {
                content.style.display = 'none';
                content.classList.remove('show', 'active');
            });

            // Show the selected tab
            const targetId = this.getAttribute('data-bs-target');
            const targetContent = document.querySelector(targetId);
            if (targetContent) {
                targetContent.style.display = 'block';
                targetContent.classList.add('show', 'active');

                // Load data for the active tab
                if (targetId === '#school') {
                    // loadEmployeeData_hr_pending();
                } else if (targetId === '#hr') {
                    // loadEmployeeData_hr();
                } else if (targetId === '#hospital') {
                    // loadEmployeeData_hr_rejected();
                } else if (targetId === '#admin') {
                    // loadEmployeeData_hr_rejected();
                }
            }
        });
    });
}
</script>