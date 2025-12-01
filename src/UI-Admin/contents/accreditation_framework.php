<?php
    // Fetch Areas
    $stmtAreas = $pdo->prepare("SELECT area_id, area_number, area_name, description
                FROM accreditation_areas
                ORDER BY area_number ASC");
    $stmtAreas->execute();
    $areas = $stmtAreas->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Indicators with Area information
    $stmtIndicators = $pdo->prepare("SELECT 
                i.indicator_id,
                i.indicator_code,
                i.indicator_description,
                i.area_id,
                a.area_number,
                a.area_name
            FROM accreditation_indicators AS i
            LEFT JOIN accreditation_areas AS a ON i.area_id = a.area_id
            ORDER BY i.indicator_code ASC");
    $stmtIndicators->execute();
    $indicatorsData = $stmtIndicators->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Criteria with Indicator information
    $stmtCriteria = $pdo->prepare("SELECT 
                c.criteria_id,
                c.criteria_number,
                c.criteria_description,
                c.required_evidence,
                c.indicator_id,
                i.indicator_code,
                a.area_name
            FROM accreditation_criteria AS c
            LEFT JOIN accreditation_indicators AS i ON c.indicator_id = i.indicator_id
            LEFT JOIN accreditation_areas AS a ON i.area_id = a.area_id
            ORDER BY c.criteria_number ASC");
    $stmtCriteria->execute();
    $criteriaData = $stmtCriteria->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Cycles
    $stmtCycles = $pdo->prepare("SELECT cycle_id, cycle_name, academic_year, status, deadline, created_at
                FROM accreditation_cycles
                ORDER BY created_at DESC");
    $stmtCycles->execute();
    $cycles = $stmtCycles->fetchAll(PDO::FETCH_ASSOC);

    $counts = 1;
?>
<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4 class=""><span class=""><i class="fa-solid fa-clipboard-list me-2"></i></span>Accreditation Framework
            </h4>
            <small class="text-muted ">Manage Areas, Indicators, Criteria and Cycles</small>
        </div>
    </div>

    <div class="row mb-2">
        <?php
            $areasCount = $pdo->query("SELECT COUNT(*) FROM accreditation_areas")->fetchColumn();
            $indicatorsCount = $pdo->query("SELECT COUNT(*) FROM accreditation_indicators")->fetchColumn();
            $criteriaCount = $pdo->query("SELECT COUNT(*) FROM accreditation_criteria")->fetchColumn();
            $cyclesCount = $pdo->query("SELECT COUNT(*) FROM accreditation_cycles")->fetchColumn();
        ?>
        <div class="col-md-3">
            <div class="card-header shadow bg-white text-center p-4 ">
                <h5 id="totalAreas"><?= $areasCount ?></h5>
                <small>Areas</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-header shadow bg-white text-center p-4 ">
                <h5 id="totalIndicators"><?= $indicatorsCount ?></h5>
                <small>Indicators</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-header shadow bg-white text-center p-4 ">
                <h5 id="totalCriteria"><?= $criteriaCount ?></h5>
                <small>Criteria</small>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body col-md-12 col-12 d-flex justify-content-between">
            <ul class="nav nav-tabs col-md-12 col-12" id="AccreditationFrameworkTab">
                <li class="nav-item col-md-2">
                    <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#areasInfo"><i
                            class="fa-solid me-2 fa-layer-group"></i>Areas</a>
                </li>
                <li class="nav-item col-md-2">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#indicatorsInfo"><i
                            class="fa-solid me-2 fa-list-check"></i>Indicators</a>
                </li>
                <li class="nav-item col-md-2">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#criteriaInfo"><i
                            class="fa-solid me-2 fa-square-check"></i>Criteria</a>
                </li>
            </ul>
        </div>
        <div class="card-body pt-0">
            <div class="tab-content">
                <!-- Areas -->
                <div class="tab-pane fade show active" id="areasInfo" role="tabpanel">
                    <!-- AREAS ADD AND SEARCH -->
                    <div class="col-md-12 col-12 d-flex justify-content-between mb-3">
                        <div class="col-md-4 col-12">
                            <input type="text" placeholder="Search by area name or number" id="searchAreas"
                                class="form-control">
                        </div>
                        <div class="col-md-3 d-flex justify-content-end">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#addAreaModal" id="add_new">
                                <i class="fa fa-plus"></i> Add New Area
                            </button>
                        </div>
                    </div>

                    <!-- ADD Area Modal -->
                    <div class="modal fade" id="addAreaModal" tabindex="-1" aria-labelledby="addAreaModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title text-white" id="addAreaModalLabel">Add New Area</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close" onclick="location.reload()"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="row g-3" id="area-form">
                                        <div class="m-2">
                                            <label class="form-label">Area Number</label>
                                            <input type="number" class="form-control" name="area_number" required>
                                        </div>
                                        <div class="m-2">
                                            <label class="form-label">Area Name</label>
                                            <input type="text" class="form-control" name="area_name" required>
                                        </div>
                                        <div class="m-2">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="3"></textarea>
                                        </div>
                                        <div class="col-12 text-center mt-3">
                                            <button type="submit" class="btn btn-danger px-5">
                                                <i class="bi bi-plus-circle me-1"></i> Add Area
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Area Modal -->
                    <div class="modal fade" id="editAreaModal" tabindex="-1" aria-labelledby="editAreaModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="edit-area-form">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title text-white" id="editAreaModalLabel">Edit Area Information
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="area_id" id="editAreaId">
                                        <div class="mb-3">
                                            <label for="editAreaNumber" class="form-label">Area Number</label>
                                            <input type="number" class="form-control" id="editAreaNumber"
                                                name="area_number" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editAreaName" class="form-label">Area Name</label>
                                            <input type="text" class="form-control" id="editAreaName" name="area_name"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editAreaDescription" class="form-label">Description</label>
                                            <textarea class="form-control" id="editAreaDescription" name="description"
                                                rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Update Area</button>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Area Modal -->
                    <div class="modal fade" id="deleteAreaModal" tabindex="-1" aria-labelledby="deleteAreaModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="delete-area-form" class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title text-white" id="deleteAreaModalLabel">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this area? This will also delete all associated
                                    indicators and criteria.
                                    <input type="hidden" name="area_id" id="deleteAreaId">
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- AREAS TABLE -->
                    <div class="table-responsive table-body">
                        <table class="table table-bordered table-sm text-center">
                            <thead class="table-light">
                                <tr style="color: #555;">
                                    <th>#</th>
                                    <th>Area Number</th>
                                    <th>Area Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" id="areasTable" style="color: #666;">
                                <?php foreach($areas as $area) : ?>
                                <tr>
                                    <td><?= $counts++ ?></td>
                                    <td><?= htmlspecialchars($area["area_number"]) ?></td>
                                    <td><?= htmlspecialchars($area["area_name"]) ?></td>
                                    <td><?= htmlspecialchars($area["description"] ?? 'N/A') ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="editArea(
                                                    <?= $area['area_id'] ?>,
                                                    '<?= addslashes($area['area_number']) ?>',
                                                    '<?= addslashes($area['area_name']) ?>',
                                                    '<?= addslashes($area['description'] ?? '') ?>'
                                                )">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteAreaModal"
                                            onclick="setDeleteAreaId(<?= $area['area_id'] ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Indicators -->
                <div class="tab-pane fade" id="indicatorsInfo" role="tabpanel">
                    <!-- INDICATORS ADD AND SEARCH -->
                    <div class="col-md-12 col-12 d-flex justify-content-between mb-3">
                        <div class="col-md-4 col-12">
                            <input type="text" placeholder="Search by indicator code or description"
                                id="searchIndicators" class="form-control">
                        </div>
                        <div class="col-md-3 d-flex justify-content-end">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#addIndicatorModal" id="add_new">
                                <i class="fa fa-plus"></i> Add New Indicator
                            </button>
                        </div>
                    </div>

                    <!-- ADD Indicator Modal -->
                    <div class="modal fade" id="addIndicatorModal" tabindex="-1"
                        aria-labelledby="addIndicatorModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title text-white" id="addIndicatorModalLabel">Add New Indicator
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close" onclick="location.reload()"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="row g-3" id="indicator-form">
                                        <div class="col-md-6">
                                            <label class="form-label">Area</label>
                                            <select name="area_id" class="form-select" required>
                                                <option value="">Select Area</option>
                                                <?php foreach($areas as $area) : ?>
                                                <option value="<?= htmlspecialchars($area["area_id"]) ?>">
                                                    <?= htmlspecialchars($area["area_number"]) . '. ' . htmlspecialchars($area["area_name"]) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Indicator Code</label>
                                            <input type="text" class="form-control" name="indicator_code" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Indicator Description</label>
                                            <textarea class="form-control" name="indicator_description" rows="4"
                                                required></textarea>
                                        </div>
                                        <div class="col-12 text-center mt-3">
                                            <button type="submit" class="btn btn-danger px-5">
                                                <i class="bi bi-plus-circle me-1"></i> Add Indicator
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Edit Indicator Modal -->
                    <div class="modal fade" id="editIndicatorModal" tabindex="-1"
                        aria-labelledby="editIndicatorModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form id="edit-indicator-form" class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title text-white" id="editIndicatorModalLabel">Edit Indicator</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <input type="hidden" name="indicator_id" id="editIndicatorId">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Area</label>
                                            <select name="area_id" id="editAreaIds" class="form-select" required>
                                                <option value="">Select Area</option>
                                                <?php foreach($areas as $area) : ?>
                                                <option value="<?= $area['area_id'] ?>">
                                                    <?= $area['area_number'] . '. ' . $area['area_name'] ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Indicator Code</label>
                                            <input type="text" class="form-control" id="editIndicatorCode"
                                                name="indicator_code" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Indicator Description</label>
                                            <textarea class="form-control" id="editIndicatorDescription"
                                                name="indicator_description" rows="4" required></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Update Indicator</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Delete Indicator Modal -->
                    <div class="modal fade" id="deleteIndicatorModal" tabindex="-1"
                        aria-labelledby="deleteIndicatorModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="delete-indicator-form" class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="deleteIndicatorModalLabel">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    Are you sure you want to delete this indicator?
                                    <input type="hidden" name="indicator_id" id="deleteIndicatorId">
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- INDICATORS TABLE -->
                    <div class="table-responsive table-body">
                        <table class="table table-bordered table-sm text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Indicator Code</th>
                                    <th>Description</th>
                                    <th>Area</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" id="indicatorsTable" style="color: #666;">
                                <?php 
                                $indicatorCount = 1;
                                foreach($indicatorsData as $indicator) : ?>
                                <tr>
                                    <td><?= $indicatorCount++ ?></td>
                                    <td><?= htmlspecialchars($indicator["indicator_code"]) ?></td>
                                    <td><?= htmlspecialchars($indicator["indicator_description"]) ?></td>
                                    <td><?= htmlspecialchars($indicator["area_number"] . '. ' . $indicator["area_name"]) ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="editIndicator('<?= $indicator['indicator_id'] ?>',
                                            '<?= $indicator['area_id'] ?>',
                                            '<?= htmlspecialchars($indicator['indicator_code']) ?>',
                                            '<?= htmlspecialchars($indicator['indicator_description']) ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="setDeleteIndicatorId('<?= $indicator['indicator_id'] ?>')"
                                            data-bs-toggle="modal" data-bs-target="#deleteIndicatorModal">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>

                                    </td>
                                </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Criteria -->
                <div class="tab-pane fade" id="criteriaInfo" role="tabpanel">
                    <!-- CRITERIA ADD AND SEARCH -->
                    <div class="col-md-12 col-12 d-flex justify-content-between mb-3">
                        <div class="col-md-4 col-12">
                            <input type="text" placeholder="Search by criteria number or description"
                                id="searchCriteria" class="form-control">
                        </div>
                        <div class="col-md-3 d-flex justify-content-end">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#addCriterionModal" id="add_new">
                                <i class="fa fa-plus"></i> Add New Criterion
                            </button>
                        </div>
                    </div>
                    <!-- ADD Criterion Modal -->
                    <div class="modal fade" id="addCriterionModal" tabindex="-1"
                        aria-labelledby="addCriterionModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title text-white" id="addCriterionModalLabel">Add New Criterion
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close" onclick="location.reload()"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="row g-3" id="criterion-form">
                                        <div class="col-md-6">
                                            <label class="form-label">Area</label>
                                            <select name="area_id" class="form-select" id="criterionArea" required>
                                                <option value="">Select Area</option>
                                                <?php foreach($areas as $area) : ?>
                                                <option value="<?= htmlspecialchars($area["area_id"]) ?>">
                                                    <?= htmlspecialchars($area["area_number"]) . '. ' . htmlspecialchars($area["area_name"]) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Indicator</label>
                                            <select name="indicator_id" class="form-select" id="criterionIndicator"
                                                required>
                                                <option value="">Select Indicator</option>
                                                <?php foreach ($indicatorsData as $indicator): ?>
                                                <option value="<?= $indicator['indicator_id'] ?>">
                                                    <?= htmlspecialchars($indicator['indicator_code']) . ' — ' . htmlspecialchars($indicator['indicator_description']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Criteria Number</label>
                                            <input type="text" class="form-control" name="criteria_number" required
                                                placeholder="e.g., 1.1.1">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Criteria Description</label>
                                            <textarea class="form-control" name="criteria_description" rows="4" required
                                                placeholder="Enter the detailed criteria description"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Required Evidence</label>
                                            <textarea class="form-control" name="required_evidence" rows="3"
                                                placeholder="List the required evidence or documentation"></textarea>
                                        </div>
                                        <div class="col-12 text-center mt-3">
                                            <button type="submit" class="btn btn-danger px-5">
                                                <i class="bi bi-plus-circle me-1"></i> Add Criterion
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Edit Criterion Modal -->
                    <div class="modal fade" id="editCriterionModal" tabindex="-1"
                        aria-labelledby="editCriterionModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form id="edit-criterion-form" class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title text-white" id="editCriterionModalLabel">Edit Criterion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <input type="hidden" name="criteria_id" id="editCriteriaId">

                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="form-label">Indicator</label>
                                            <select name="indicator_id" id="editIndicatorIdSelect" class="form-select"
                                                required>
                                                <option value="">Select Indicator</option>
                                                <?php foreach($indicatorsData as $indicator) : ?>
                                                <option value="<?= $indicator['indicator_id'] ?>">
                                                    <?= htmlspecialchars($indicator['indicator_code']) . ' — ' . htmlspecialchars($indicator['indicator_description']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Criteria Number</label>
                                            <input type="text" class="form-control" id="editCriteriaNumber"
                                                name="criteria_number" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Criteria Description</label>
                                            <textarea class="form-control" id="editCriteriaDescription"
                                                name="criteria_description" rows="4" required></textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Required Evidence</label>
                                            <textarea class="form-control" id="editRequiredEvidence"
                                                name="required_evidence" rows="3"></textarea>
                                        </div>

                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Update Criterion</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Criterion Modal -->
                    <div class="modal fade" id="deleteCriterionModal" tabindex="-1"
                        aria-labelledby="deleteCriterionModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="delete-criterion-form" class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="deleteCriterionModalLabel">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    Are you sure you want to delete this criterion?
                                    <input type="hidden" name="criteria_id" id="deleteCriteriaId">
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- CRITERIA TABLE -->
                    <div class="table-responsive table-body">
                        <table class="table table-bordered table-sm text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Criteria Number</th>
                                    <th>Description</th>
                                    <th>Required Evidence</th>
                                    <th>Indicator</th>
                                    <th>Area</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" id="criteriaTable" style="color: #666;">
                                <?php 
                                $criteriaCount = 1;
                                foreach($criteriaData as $criterion) : ?>
                                <tr>
                                    <td><?= $criteriaCount++ ?></td>
                                    <td><?= htmlspecialchars($criterion["criteria_number"]) ?></td>
                                    <td><?= htmlspecialchars($criterion["criteria_description"]) ?></td>
                                    <td><?= htmlspecialchars($criterion["required_evidence"] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($criterion["indicator_code"]) ?></td>
                                    <td><?= htmlspecialchars($criterion["area_name"]) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary edit-criterion-btn"
                                            data-criteria-id="<?= htmlspecialchars($criterion['criteria_id']) ?>"
                                            data-indicator-id="<?= htmlspecialchars($criterion['indicator_id']) ?>"
                                            data-criteria-number="<?= htmlspecialchars($criterion['criteria_number']) ?>"
                                            data-criteria-description="<?= htmlspecialchars($criterion['criteria_description']) ?>"
                                            data-required-evidence="<?= htmlspecialchars($criterion['required_evidence']) ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <button type="button" class="btn btn-sm btn-danger delete-criterion-btn"
                                            data-criteria-id="<?= htmlspecialchars($criterion['criteria_id']) ?>"
                                            data-bs-toggle="modal" data-bs-target="#deleteCriterionModal">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// JavaScript functions for Areas
function editArea(areaId, areaNumber, areaName, description) {
    document.getElementById('editAreaId').value = areaId;
    document.getElementById('editAreaNumber').value = areaNumber;
    document.getElementById('editAreaName').value = areaName;
    document.getElementById('editAreaDescription').value = description;

    // Show the modal
    var editModal = new bootstrap.Modal(document.getElementById('editAreaModal'));
    editModal.show();
}

function setDeleteAreaId(areaId) {
    document.getElementById('deleteAreaId').value = areaId;
}

function editIndicator(indicatorId, areaId, indicatorCode, indicatorDescription) {
    document.getElementById('editIndicatorId').value = indicatorId;
    document.getElementById('editAreaIds').value = areaId;
    document.getElementById('editIndicatorCode').value = indicatorCode;
    document.getElementById('editIndicatorDescription').value = indicatorDescription;

    var editModal = new bootstrap.Modal(document.getElementById('editIndicatorModal'));
    editModal.show();
}

function setDeleteIndicatorId(indicatorId) {
    document.getElementById('deleteIndicatorId').value = indicatorId;
}

// Edit button event handler
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit buttons
    document.querySelectorAll('.edit-criterion-btn').forEach(button => {
        button.addEventListener('click', function() {
            const criteriaId = this.getAttribute('data-criteria-id');
            const indicatorId = this.getAttribute('data-indicator-id');
            const criteriaNumber = this.getAttribute('data-criteria-number');
            const criteriaDescription = this.getAttribute('data-criteria-description');
            const requiredEvidence = this.getAttribute('data-required-evidence');

            document.getElementById('editCriteriaId').value = criteriaId;
            document.getElementById('editIndicatorIdSelect').value = indicatorId;
            document.getElementById('editCriteriaNumber').value = criteriaNumber;
            document.getElementById('editCriteriaDescription').value = criteriaDescription;
            document.getElementById('editRequiredEvidence').value = requiredEvidence;

            var modal = new bootstrap.Modal(document.getElementById('editCriterionModal'));
            modal.show();
        });
    });

    // Handle delete buttons
    document.querySelectorAll('.delete-criterion-btn').forEach(button => {
        button.addEventListener('click', function() {
            const criteriaId = this.getAttribute('data-criteria-id');
            document.getElementById('deleteCriteriaId').value = criteriaId;
        });
    });
});

// Search functionality for Areas
document.addEventListener('DOMContentLoaded', function() {
    const searchAreas = document.getElementById('searchAreas');

    if (searchAreas) {
        searchAreas.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            searchTable('areasTable', searchTerm);
        });
    }

    // Add similar search functionality for other tables
    const searchIndicators = document.getElementById('searchIndicators');
    if (searchIndicators) {
        searchIndicators.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            searchTable('indicatorsTable', searchTerm);
        });
    }

    const searchCriteria = document.getElementById('searchCriteria');
    if (searchCriteria) {
        searchCriteria.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            searchTable('criteriaTable', searchTerm);
        });
    }

    const searchCycles = document.getElementById('searchCycles');
    if (searchCycles) {
        searchCycles.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            searchTable('cyclesTable', searchTerm);
        });
    }

    function searchTable(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = table.getElementsByTagName('tr');
        let hasVisibleRows = false;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            if (cells.length === 0) continue;

            // Reset cell contents first
            resetRowHighlights(row);

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];

                // Skip action cells
                if (cell.querySelector('button') || cell.querySelector('a')) {
                    continue;
                }

                const originalText = cell.textContent;
                const lowerText = originalText.toLowerCase();

                if (lowerText.includes(searchTerm)) {
                    found = true;
                    hasVisibleRows = true;

                    // Highlight the matched text
                    const highlightedText = originalText.replace(
                        new RegExp(searchTerm, 'gi'),
                        match => `<span class="search-highlight">${match}</span>`
                    );
                    cell.innerHTML = highlightedText;
                }
            }

            row.style.display = found ? '' : 'none';
        }

        // Show "no results" message if needed
        showNoResultsMessage(tableId, hasVisibleRows);
    }

    function resetRowHighlights(row) {
        const cells = row.getElementsByTagName('td');
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell.querySelector('.search-highlight')) {
                cell.innerHTML = cell.textContent;
            }
        }
    }

    function showNoResultsMessage(tableId, hasVisibleRows) {
        let noResultsRow = document.getElementById(`no-results-${tableId}`);

        if (!hasVisibleRows && !noResultsRow) {
            const table = document.getElementById(tableId);
            noResultsRow = document.createElement('tr');
            noResultsRow.id = `no-results-${tableId}`;
            noResultsRow.innerHTML =
                `<td colspan="10" class="text-center text-muted py-3">No records found matching your search</td>`;
            table.appendChild(noResultsRow);
        } else if (hasVisibleRows && noResultsRow) {
            noResultsRow.remove();
        }
    }
});

// Add CSS for highlighting
if (!document.querySelector('#search-highlight-style')) {
    const style = document.createElement('style');
    style.id = 'search-highlight-style';
    style.textContent = `
            .search-highlight {
                background-color: #ffeb3b;
                padding: 2px 4px;
                border-radius: 3px;
                font-weight: bold;
                color: #333;
            }
        `;
    document.head.appendChild(style);
}
document.addEventListener('DOMContentLoaded', function() {
    initAccreditationFrameworkTabs();
});

function initAccreditationFrameworkTabs() {
    const tabButtons = document.querySelectorAll('#AccreditationFrameworkTab .nav-link');
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
                if (targetId === '#areasInfo') {

                } else if (targetId === '#indicatorsInfo') {

                } else if (targetId === '#criteriaInfo') {

                } else if (targetId === '#cyclesInfo') {

                }
            }
        });
    });
}
</script>