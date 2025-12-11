<?php
$departmentSTMT = $pdo->prepare("SELECT departments.department_id FROM departments 
INNER JOIN users ON departments.department_id = users.department_id
WHERE users.user_id = :user_id");
$departmentSTMT->execute([
    'user_id' => $user_id
]);
$departmentData = $departmentSTMT->fetch(PDO::FETCH_ASSOC);

$department_id = $departmentData["department_id"];

try {
    // Check if required tables exist
    $tablesExist = $pdo->query("SHOW TABLES LIKE 'departments'")->fetch();
    if(!$tablesExist) {
        throw new Exception("Required tables are not created yet. Please run the database setup.");
    }

    // Get the specific college/department for this user
    $collegeStmt = $pdo->prepare("
        SELECT d.*, COUNT(p.program_id) as program_count
        FROM departments d
        LEFT JOIN programs p ON d.department_id = p.department_id
        WHERE d.department_id = :department_id
        GROUP BY d.department_id
        ORDER BY d.department_name
    ");
    
    $collegeStmt->execute(['department_id' => $department_id]);
    $colleges = $collegeStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $colleges = [];
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
    $colleges = [];
}
?>

<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4><span><i class="fa-solid fa-clipboard-check"></i></span> Program Accreditation Status</h4>
            <small class="text-muted">Track program accreditation levels and survey dates</small>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-success border-start border-4">
                <div class="card-body">
                    <h6 class="card-title text-success"><i class="fas fa-check-circle"></i> Level IV</h6>
                    <h3 id="level4Count" class="card-text">0</h3>
                    <p class="card-text text-muted small">Highest Accreditation</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-primary border-start border-4">
                <div class="card-body">
                    <h6 class="card-title text-primary"><i class="fas fa-chart-line"></i> Level III</h6>
                    <h3 id="level3Count" class="card-text">0</h3>
                    <p class="card-text text-muted small">Re-accredited</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-warning border-start border-4">
                <div class="card-body">
                    <h6 class="card-title text-warning"><i class="fas fa-flag"></i> Level II</h6>
                    <h3 id="level2Count" class="card-text">0</h3>
                    <p class="card-text text-muted small">Accredited</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-secondary border-start border-4">
                <div class="card-body">
                    <h6 class="card-title text-secondary"><i class="fas fa-rocket"></i> Level I</h6>
                    <h3 id="level1Count" class="card-text">0</h3>
                    <p class="card-text text-muted small">Candidate Status</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-6">
            <select id="filterCollege" class="form-select form-select-sm">
                <option value="">All Colleges/Schools</option>
                <?php if(is_array($colleges)): ?>
                <?php foreach($colleges as $college): ?>
                <option value="<?= $college['department_id'] ?>">
                    <?= htmlspecialchars($college['department_name']) ?> (<?= $college['program_count'] ?>)
                </option>
                <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-6">
            <div class="input-group">
                <input type="text" placeholder="Search programs..." id="searchPrograms"
                    class="form-control form-control-sm">
            </div>
        </div>
    </div>

    <!-- Main Accreditation Display (Like ZPPSU Website) -->
    <div id="accreditationDisplay">
        <?php if(is_array($colleges) && count($colleges) > 0): ?>
        <?php foreach($colleges as $college): ?>
        <?php
            try {
                // Get programs for this college
                $programsStmt = $pdo->prepare("
                    SELECT p.*,
                        COALESCE(
                            (SELECT pa.accreditation_level 
                            FROM program_accreditation pa 
                            WHERE pa.program_id = p.program_id 
                            ORDER BY pa.survey_date DESC 
                            LIMIT 1),
                            'not_accredited'
                        ) as current_level,
                        (SELECT pa.survey_date 
                        FROM program_accreditation pa 
                        WHERE pa.program_id = p.program_id 
                        ORDER BY pa.survey_date DESC 
                        LIMIT 1) as survey_date
                    FROM programs p
                    WHERE p.department_id = ?
                    ORDER BY p.program_name
                ");
                $programsStmt->execute([$college['department_id']]);
                $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $programs = [];
                echo "<div class='alert alert-warning mb-2'>Error loading programs: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            
            if (count($programs) > 0):
        ?>
        <div class="card mb-4 college-card" data-college-id="<?= $college['department_id'] ?>">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>
                    <?= htmlspecialchars($college['department_name']) ?>
                    <span class="badge bg-secondary float-end"><?= count($programs) ?> programs</span>
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover text-center" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th width="40%">Program Name</th>
                            <th width="20%">Accreditation Level</th>
                            <th width="25%">Survey Visit Date</th>
                            <th width="15%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($programs as $program): 
                            $levelClass = getLevelBadgeClass($program['current_level']);
                            $surveyDate = $program['survey_date'] ? date('F j, Y', strtotime($program['survey_date'])) : 'Not scheduled';
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($program['program_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($program['program_code']) ?></small>
                            </td>
                            <td>
                                <?php if($program['current_level'] && $program['current_level'] != 'not_accredited'): ?>
                                <span class="badge <?= $levelClass ?>">
                                    <?= formatLevelText($program['current_level']) ?>
                                </span>
                                <?php else: ?>
                                <span class="badge bg-light text-dark">Not Accredited</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($program['survey_date']): ?>
                                <?= $surveyDate ?>
                                <?php if(isUpcomingSurvey($program['survey_date'])): ?>
                                <br><small class="text-danger"><i class="fas fa-exclamation-circle"></i>
                                    Upcoming</small>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">No date set</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="index.php?page=contents/program_history&program_id=<?= $program['program_id'] ?>" class="btn btn-info btn-sm m-0">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <button class="btn btn-warning btn-sm m-0"
                                        onclick="viewNextLevelInfo(<?= $program['program_id'] ?>, '<?= addslashes($program['current_level'] ?? '') ?>')">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No colleges/departments found. Please run the database setup.
        </div>
        <?php endif; ?>
    </div>

    <!-- Add/Update Accreditation Modal -->
    <div class="modal fade" id="addAccreditationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header  bg-gradient-primary text-white">
                    <h5 class="modal-title text-white"><i class="fas fa-award"></i> Update Accreditation Status</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="add-accreditation-form">
                    <div class="modal-body">
                        <input type="hidden" name="program_id" id="accreditationProgramId">
                        <div class="mb-3">
                            <label class="form-label">Program</label>
                            <select name="program_id_select" id="programSelect" class="form-select" required>
                                <option value="">Select Program</option>
                                <?php 
                                try {
                                    $allPrograms = $pdo->query("SELECT p.*, d.department_name FROM programs p JOIN departments d ON p.department_id = d.department_id ORDER BY d.department_name, p.program_name")->fetchAll();
                                    foreach($allPrograms as $program): ?>
                                    <option value="<?= $program['program_id'] ?>">
                                        <?= htmlspecialchars($program['department_name'] . ' - ' . $program['program_name']) ?>
                                    </option>
                                    <?php endforeach;
                                } catch (PDOException $e) {
                                    echo "<option value='' disabled>Error loading programs</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Accreditation Level</label>
                            <select name="accreditation_level" class="form-select" required>
                                <option value="">Select Level</option>
                                <option value="1">Level I - Candidate Status</option>
                                <option value="2">Level II - Accredited</option>
                                <option value="3">Level III - Re-accredited</option>
                                <option value="4">Level IV - Center of Excellence/Development</option>
                                <option value="phase1">Phase 1</option>
                                <option value="phase2">Phase 2</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Survey Visit Date</label>
                            <input type="date" name="survey_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Accrediting Body</label>
                            <input type="text" name="accrediting_body" class="form-control" value="AACCUP" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cycle</label>
                            <select name="cycle_id" id="cycle_id" class="form-select" required>
                                <option value="">Select Cycle</option>
                                <?php 
                                try {
                                    // FIXED: Changed from "cycles" to "accreditation_cycles"
                                    $allCycles = $pdo->query("SELECT * FROM accreditation_cycles ORDER BY academic_year DESC")->fetchAll();
                                    foreach($allCycles as $cycle): ?>
                                    <option value="<?= $cycle['cycle_id'] ?>">
                                        <?= htmlspecialchars($cycle['cycle_name'] . ' - ' . $cycle['academic_year']) ?>
                                    </option>
                                    <?php endforeach;
                                } catch (PDOException $e) {
                                    echo "<option value='' disabled>Error loading cycles</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2"
                                placeholder="e.g., Revisit for Areas 2-9, Initial Survey, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Save Accreditation</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View History Modal -->
    <div class="modal fade" id="viewHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="historyProgramName"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="accreditationTimeline">
                        <!-- Timeline will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Level Info Modal -->
    <div class="modal fade" id="nextLevelModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white"><i class="fas fa-arrow-up"></i> Next Level Requirements</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="nextLevelContent">
                    <!-- Requirements will be loaded here -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading requirements...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateAccreditation(currentProgramId)"
                        id="updateAccreditationBtn" style="display: none;">
                        <i class="fas fa-edit me-1"></i> Update Accreditation
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Helper functions
function getLevelBadgeClass($level) {
    $classes = [
        '1' => 'bg-secondary',
        '2' => 'bg-primary',
        '3' => 'bg-success',
        '4' => 'bg-warning text-dark',
        'phase1' => 'bg-info',
        'phase2' => 'bg-dark'
    ];
    return $classes[$level] ?? 'bg-light text-dark';
}

function formatLevelText($level) {
    $texts = [
        '1' => 'Level I',
        '2' => 'Level II',
        '3' => 'Level III',
        '4' => 'Level IV',
        'phase1' => 'Phase 1',
        'phase2' => 'Phase 2'
    ];
    return $texts[$level] ?? $level;
}

function isUpcomingSurvey($date) {
    try {
        $today = new DateTime();
        $surveyDate = new DateTime($date);
        $interval = $today->diff($surveyDate);
        return $surveyDate > $today && $interval->days <= 60;
    } catch (Exception $e) {
        return false;
    }
}
?>
<script>
$(document).ready(function() {
    // Update summary counts
    function updateSummaryCounts() {
        console.log("Fetching summary counts...");

        $.ajax({
            url: base_url + "authentication/action.php?action=GetSummaryCounts",
            method: 'GET',
            dataType: 'json', // Explicitly expect JSON
            success: function(response) {
                console.log("Success response:", response);

                if (response && response.status === 1 && response.data) {
                    const data = response.data;
                    $('#level4Count').text(data.level4 || 0);
                    $('#level3Count').text(data.level3 || 0);
                    $('#level2Count').text(data.level2 || 0);
                    $('#level1Count').text(data.level1 || 0);

                    // Optional: Update other elements
                    console.log("Summary loaded:", data);
                } else {
                    console.error("Server returned error:", response);
                    showToast(response?.message || 'Failed to load summary', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });

                showToast('Failed to load summary counts', 'error');
            }
        });
    }

    // Rest of your JavaScript code remains the same...
    // Filter programs
    $('#filterCollege, #searchPrograms').on('change keyup', function() {
        const collegeId = $('#filterCollege').val();
        const searchTerm = $('#searchPrograms').val().toLowerCase();

        $('.college-card').each(function() {
            const card = $(this);
            const cardCollegeId = card.data('college-id');
            let showCard = true;

            // Filter by college
            if (collegeId && cardCollegeId != collegeId) {
                showCard = false;
            }

            // Filter by search term
            if (searchTerm) {
                const cardText = card.text().toLowerCase();
                if (!cardText.includes(searchTerm)) {
                    showCard = false;
                }
            }

            card.toggle(showCard);
        });
    });

    // Add accreditation form
    $('#add-accreditation-form').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: base_url + "authentication/action.php?action=add_accreditation_form",
            method: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function(jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function() {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Profile Updated');
            }
        });
    });

    // When program select changes, update hidden field
    $('#programSelect').change(function() {
        $('#accreditationProgramId').val($(this).val());
    });

    // Initial load
    updateSummaryCounts();

    // Toast notification
    function showToast(message, type) {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed bottom-0 end-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        setTimeout(() => toast.remove(), 3000);
    }

    // Global functions (keep your existing ones)
    window.viewProgramHistory = function(programId) {
        console.log("Loading program history for ID:", programId);

        $.ajax({
            url: base_url + "authentication/action.php?action=viewProgramHistory",
            method: 'POST',
            dataType: 'json',
            data: {
                program_id: programId
            },
            success: function(response) {
                console.log("History response:", response);

                if (response && response.status === 1) {
                    // Set modal title
                    $('#historyProgramName').html(`
                        <i class="fas fa-history me-2"></i>
                        <strong>${response.program_name}</strong>
                        <small class="text-muted"></small>
                        <br>
                        <small class="text-muted">${response.department_name} </small>
                    `);

                    let timelineHtml = '';

                    if (response.history && response.history.length > 0) {
                        // Add current status banner
                        if (response.current_accreditation) {
                            timelineHtml += `
                                <div class="alert alert-success mb-4">
                                    <h6 class="mb-1"><i class="fas fa-star"></i> Current Status</h6>
                                    <p class="mb-1"><strong>Level:</strong> ${response.current_accreditation.level}</p>
                                    <p class="mb-1 text-dark"><strong>Last Survey:</strong> ${formatDate(response.current_accreditation.survey_date)}</p>
                                    ${response.current_accreditation.remarks ? `<p class="mb-0"><strong>Remarks:</strong> ${response.current_accreditation.remarks}</p>` : ''}
                                </div>
                            `;
                        }

                        timelineHtml +=
                            '<h6 class="mb-3"><i class="fas fa-timeline me-2"></i> Accreditation Timeline</h6>';
                        timelineHtml += '<div class="timeline">';

                        response.history.forEach((record, index) => {
                            const isCurrent = response.current_accreditation &&
                                response.current_accreditation.survey_date === record
                                .survey_date;

                            timelineHtml += `
                                <div class="timeline-item ${index % 2 === 0 ? 'left' : 'right'} ${isCurrent ? 'current' : ''}">
                                    <div style="width: 100% !important; margin: 0 !important;" class="timeline-content ${isCurrent ? 'border-success border-2' : ''}">
                                        <div class="timeline-header">
                                            <h6 class="mb-1">${record.accreditation_level}</h6>
                                            <small class="text-muted">${formatDate(record.survey_date)}</small>
                                        </div>
                                        <div class="timeline-body">
                                            <p class="mb-1 text-dark"><strong>Accrediting Body:</strong> ${record.accrediting_body || 'AACCUP'}</p>
                                            ${record.remarks ? `<p class="mb-2 text-dark"><strong>Remarks:</strong> ${record.remarks}</p>` : ''}
                                            <div class="timeline-footer">
                                                <small class="text-dark">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Recorded: ${formatDate(record.created_at)}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        timelineHtml += '</div>';

                        // Add summary
                        timelineHtml += `
                            <div class="mt-4 pt-3 border-top">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1 text-dark"><strong>Total Accreditation Records:</strong> ${response.total_records}</p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">Last updated: ${new Date().toLocaleDateString()}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        timelineHtml = `
                            <div class="text-center py-5">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <h5>No Accreditation History</h5>
                                <p class="text-muted">This program has no accreditation records yet.</p>
                                <button class="btn btn-primary mt-2" onclick="updateAccreditation(${programId})">
                                    <i class="fas fa-plus"></i> Add First Accreditation
                                </button>
                            </div>
                        `;
                    }

                    $('#accreditationTimeline').html(timelineHtml);
                    $('#viewHistoryModal').modal('show');

                } else {
                    console.error("Error from server:", response);
                    showToast(response?.message || 'Failed to load program history', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                showToast('Failed to load program history', 'error');
            }
        });
    };

    // Format date function (make sure this exists)
    function formatDate(dateString) {
        if (!dateString || dateString === '0000-00-00') return 'N/A';

        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return 'Invalid Date';

            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (e) {
            return dateString;
        }
    }

    window.updateAccreditation = function(programId) {
        $('#accreditationProgramId').val(programId);
        $('#programSelect').val(programId);
        $('#addAccreditationModal').modal('show');
    };

    window.viewNextLevelInfo = function(programId, currentLevel) {
        console.log("Getting next level info for:", {
            programId,
            currentLevel
        });

        $.ajax({
            url: base_url + "authentication/action.php?action=viewNextLevelInfo",
            method: 'POST',
            dataType: 'json',
            data: {
                program_id: programId,
                current_level: currentLevel
            },
            success: function(response) {
                console.log("Next level response:", response);

                if (response && response.status === 1) {
                    let html = `<h5 class="text-dark">${response.program_name}</h5>`;
                    html +=
                        `<p class="text-muted small">${response.department_name} - ${response.program_code}</p>`;
                    html +=
                        `<hr><h6 class="text-dark">Current Status: <span class="badge bg-primary">${response.current_level}</span></h6>`;

                    if (response.current_level_desc) {
                        html += `<p class="text-dark mb-3">${response.current_level_desc}</p>`;
                    }

                    if (response.next_level) {
                        html += `
                            <div class="alert alert-info mt-3">
                                <h6><i class="fas fa-arrow-up me-2"></i>Next Level: <strong>${response.next_level.level_name}</strong></h6>
                                ${response.next_level.description ? `<p class="mb-3">${response.next_level.description}</p>` : ''}
                                <hr>
                                <h6><i class="fas fa-list-check me-2"></i>Requirements:</h6>
                                <div class="requirements-list mt-2">
                                    ${formatRequirements(response.next_level.requirements)}
                                </div>
                                <div class="mt-3 small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    This information is based on accreditation standards and may be subject to change.
                                </div>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="alert alert-success mt-3">
                                <i class="fas fa-trophy me-2"></i> 
                                <strong>Congratulations!</strong> This program has achieved the highest accreditation level!
                                <p class="mt-2 mb-0">Maintain current standards for continuous accreditation.</p>
                            </div>
                        `;
                    }

                    $('#nextLevelContent').html(html);
                    $('#nextLevelModal').modal('show');
                } else {
                    console.error("Error from server:", response);
                    showToast(response?.message || 'Failed to load next level info', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                showToast('Failed to load next level information', 'error');
            }
        });
    };

    function formatRequirements(requirements) {
        if (!requirements || requirements.trim() === '') {
            return '<div class="text-center py-3"><i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i><p class="text-muted"><em>No specific requirements listed</em></p></div>';
        }

        // If it's a simple string, split by newlines or other delimiters
        if (typeof requirements === 'string') {
            const lines = requirements.split(/\n|•|\*|;|,/).filter(line => line.trim());
            if (lines.length > 1) {
                return '<ul class="list-group list-group-flush">' +
                    lines.map(line =>
                        `<li class="list-group-item border-0 py-1"><i class="fas fa-check-circle text-success me-2"></i>${line.trim()}</li>`
                        ).join('') +
                    '</ul>';
            }
            return `<div class="p-3 border rounded bg-light">${requirements}</div>`;
        }

        return '<p class="text-muted"><em>Requirements data format error</em></p>';
    }



    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function formatLevelDisplay(level) {
        // Convert level codes to readable format
        const levelMap = {
            '1': 'Level I',
            '2': 'Level II',
            '3': 'Level III',
            '4': 'Level IV',
            'i': 'Level I',
            'ii': 'Level II',
            'iii': 'Level III',
            'iv': 'Level IV'
        };
        return levelMap[level.toLowerCase()] || level;
    }

    function formatRequirements(requirements) {
        if (!requirements) return '<p class="text-muted">No specific requirements listed</p>';

        // If requirements contain HTML, return as-is
        if (requirements.includes('<') && requirements.includes('>')) {
            return requirements;
        }

        // If requirements contain bullet points or line breaks, format them
        const lines = requirements.split(/\n|•|;|,/).filter(line => line.trim());
        if (lines.length > 1) {
            return '<ul>' + lines.map(line => `<li>${line.trim()}</li>`).join('') + '</ul>';
        }

        return `<p>${requirements}</p>`;
    }

    // Toast notification
    function showToast(message, type) {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed bottom-0 end-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        setTimeout(() => toast.remove(), 3000);
    }
});
</script>
<style>
.timeline {
    position: relative;
    max-width: 100%;
    margin: 0 auto;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    width: 3px;
    background-color: #dee2e6;
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -1.5px;
}

.timeline-item {
    padding: 10px 40px;
    position: relative;
    width: 50%;
    box-sizing: border-box;
}

.timeline-item.left {
    left: 0;
    padding-right: 50px;
}

.timeline-item.right {
    left: 50%;
    padding-left: 50px;
}

.timeline-item::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background-color: white;
    border: 3px solid #0d6efd;
    border-radius: 50%;
    top: 15px;
    z-index: 1;
}

.timeline-item.left::after {
    right: -10px;
}

.timeline-item.right::after {
    left: -10px;
}

.timeline-item.current::after {
    border-color: #198754;
    background-color: #198754;
}

.timeline-content {
    padding: 15px;
    background-color: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.timeline-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
    margin-bottom: 10px;
}

.timeline-header h6 {
    color: #0d6efd;
    margin: 0;
    font-size: 1rem;
}

.timeline-item.current .timeline-header h6 {
    color: #198754;
}

.timeline-body {
    font-size: 0.875rem;
}

.timeline-footer {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 10px;
}

@media screen and (max-width: 768px) {
    .timeline::before {
        left: 31px;
    }

    .timeline-item {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
    }

    .timeline-item.right {
        left: 0%;
    }

    .timeline-item::after {
        left: 21px;
    }

    .timeline-item.left {
        padding-right: 25px;
    }
}
</style>