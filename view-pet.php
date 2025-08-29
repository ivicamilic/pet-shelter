<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$lang = $_SESSION['lang'] ?? 'en';
$L = require __DIR__ . '/lang/' . $lang . '.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header('Location: pets.php');
    exit();
}

$pet = getPetById($_GET['id']);
if (!$pet) {
    header('Location: pets.php');
    exit();
}

$health_checks = $db->fetchAll("SELECT * FROM health_checks WHERE pet_id = ? ORDER BY check_date DESC, id DESC", [$pet['id']]);

include 'includes/header.php';
?>

<div class="container mt-4">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo $L['pet_details'] ?? 'Pet Details'; ?>: <?php echo htmlspecialchars($pet['name']); ?></h2>
        <a href="pets.php" class="btn btn-secondary"><?php echo $L['back_to_all_pets'] ?? 'Back to All Pets'; ?></a>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <?php if ($pet['image_path']): ?>
                    <a href="<?php echo htmlspecialchars($pet['image_path']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                    </a>
                <?php else: ?>
                    <div class="text-center py-5 bg-light">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                        <p><?php echo $L['no_image_available'] ?? 'No image available'; ?></p>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($pet['name']); ?></h5>
                    <p class="card-text">
                        <strong><?php echo $L['status'] ?? 'Status'; ?>:</strong> 
                        <span class="badge 
                            <?php 
                            switch($pet['status']) {
                                case 'available': echo 'bg-success'; break;
                                case 'adopted': echo 'bg-secondary'; break;
                                case 'fostered': echo 'bg-info'; break;
                                case 'medical': echo 'bg-warning'; break;
                                default: echo 'bg-light text-dark';
                            }
                            ?>">
                            <?php echo $L[$pet['status']] ?? ucfirst($pet['status']); ?>
                        </span>
                    </p>
                    <p class="card-text">
                        <strong><?php echo $L['presence_in_shelter'] ?? 'Presence in Shelter'; ?>:</strong>
                            <?php echo $pet['in_shelter'] ? '<span class="text-success">' . ($L['yes'] ?? 'Yes') . '</span>' : '<span class="text-danger">' . ($L['no'] ?? 'No') . '</span>'; ?>
                    </p>
                    <p class="card-text">
                        <strong><?php echo $L['incoming_date'] ?? 'Incoming Date'; ?>:</strong>
                            <?php echo !empty($pet['incoming_date']) 
                                ? date('d.m.Y', strtotime($pet['incoming_date'])) 
                                : '<span class="text-muted">' . ($L['not_available'] ?? 'N/A') . '</span>'; ?>
                    </p>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong><?php echo $L['species'] ?? 'Species'; ?>:</strong> <?php echo $L[$pet['species']] ?? ucfirst(htmlspecialchars($pet['species'])); ?>
                    </li>
                    <li class="list-group-item">
                        <strong><?php echo $L['breed'] ?? 'Breed'; ?>:</strong> <?php echo htmlspecialchars($pet['breed']); ?>
                    </li>
                    <li class="list-group-item">
                        <strong><?php echo $L['sex'] ?? 'Sex'; ?>:</strong> <?php echo $L[$pet['sex']] ?? ucfirst(htmlspecialchars($pet['sex'])); ?>
                    </li>
                    <li class="list-group-item">
                        <strong><?php echo $L['date_of_birth'] ?? 'Date of Birth'; ?>:</strong> 
                        <?php echo !empty($pet['birth_date']) ? date('d.m.Y', strtotime($pet['birth_date'])) : '<span class="text-muted">' . ($L['not_available'] ?? 'N/A') . '</span>'; ?>                  </li>
                </ul>
                <div class="card-body">
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                        <a href="edit-pet.php?id=<?php echo $pet['id']; ?>" class="card-link btn btn-warning"><?php echo $L['edit'] ?? 'Edit'; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><?php echo $L['identification'] ?? 'Identification'; ?></h5>
                </div>
                <div class="card-body">
                    <p><strong><?php echo $L['microchip_number'] ?? 'Microchip Number'; ?>:</strong> <?php echo htmlspecialchars($pet['microchip_number'] ?: 'Not available'); ?></p>
                    <p><strong><?php echo $L['microchip_date'] ?? 'Microchip Date'; ?>:</strong> <?php echo !empty($pet['microchip_date']) ? date('d.m.Y', strtotime($pet['microchip_date'])) : 'Not available'; ?></p>
                    <p><strong><?php echo $L['microchip_location'] ?? 'Microchip Location'; ?>:</strong> <?php echo htmlspecialchars($pet['microchip_location'] ?: 'Not available'); ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><?php echo $L['vaccinations'] ?? 'Vaccinations'; ?></h5>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                        <?php if (empty($pet['vaccinations'])): ?>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVaccinationModal">
                                <?php echo $L['add_vaccination'] ?? 'Add Vaccination'; ?>
                            </button>
                        <?php else: ?>
                            <a href="#" 
                               class="btn btn-sm btn-warning"
                               id="editVaccinationBtn"
                               data-bs-toggle="modal"
                               data-bs-target="#editVaccinationModal"
                               data-vaccinations='<?php echo json_encode($pet['vaccinations']); ?>'>
                               <?php echo $L['edit_vaccination'] ?? 'Edit Vaccination'; ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($pet['vaccinations'])): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?php echo $L['type'] ?? 'Type'; ?></th>
                                        <th><?php echo $L['name'] ?? 'Name'; ?></th>
                                        <th><?php echo $L['date'] ?? 'Date'; ?></th>
                                        <th><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></th>
                                        <th><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pet['vaccinations'] as $vaccination): ?>
                                        <tr>
                                            <td><?php echo ucfirst(htmlspecialchars($vaccination['vaccine_type'])); ?></td>
                                            <td><?php echo htmlspecialchars($vaccination['vaccine_name']); ?></td>
                                            <td><?php echo date('d.m.Y', strtotime($vaccination['vaccination_date'])); ?></td>
                                            <td><?php echo $vaccination['expiry_date'] ? date('d.m.Y', strtotime($vaccination['expiry_date'])) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($vaccination['veterinarian'] ?: 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p><?php echo $L['no_vaccination_records'] ?? 'No vaccination records available.'; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><?php echo $L['health_checks'] ?? 'Health Checks'; ?></h5>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                        <?php if (empty($health_checks)): ?>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addHealthCheckModal">
                                <?php echo $L['add_health_check'] ?? 'Add Health Check'; ?>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-sm btn-warning" id="editHealthCheckBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#editHealthCheckModal"
                                data-health-checks='<?php echo json_encode($health_checks); ?>'>
                                <?php echo $L['edit_health_check'] ?? 'Edit Health Check'; ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($health_checks)): ?>
                        <div class="accordion" id="healthChecksAccordion">
                            <?php foreach ($health_checks as $index => $check): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                            <?php echo isset($check['check_date']) && $check['check_date'] ? date('d.m.Y', strtotime($check['check_date'])) : 'No date'; ?>
                                            <?php if (isset($check['health_status'])): ?>
                                                - <span class="ms-2 badge 
                                                    <?php 
                                                    switch($check['health_status']) {
                                                        case 'excellent': echo 'bg-success'; break;
                                                        case 'good': echo 'bg-primary'; break;
                                                        case 'fair': echo 'bg-info'; break;
                                                        case 'poor': echo 'bg-warning'; break;
                                                        case 'critical': echo 'bg-danger'; break;
                                                        default: echo 'bg-light text-dark';
                                                    }
                                                    ?>">
                                                    <?php echo $L[$check['health_status']] ?? ucfirst($check['health_status']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#healthChecksAccordion">
                                        <div class="accordion-body">
                                            <?php if (!empty($check['veterinarian'])): ?>
                                                <p><strong><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?>:</strong> <?php echo htmlspecialchars($check['veterinarian']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['diagnosis'])): ?>
                                                <p><strong><?php echo $L['diagnosis'] ?? 'Diagnosis'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['diagnosis'])); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['treatment_plan'])): ?>
                                                <p><strong><?php echo $L['treatment_plan'] ?? 'Treatment Plan'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['treatment_plan'])); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['clinical_exam'])): ?>
                                                <p><strong><?php echo $L['clinical_exam'] ?? 'Clinical Exam'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['clinical_exam'])); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['animal_statement'])): ?>
                                                <p><strong><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['animal_statement'])); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['condition'])): ?>
                                                <p><strong><?php echo $L['condition'] ?? 'Condition'; ?>:</strong> <?php echo htmlspecialchars($check['condition']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['health_notes'])): ?>
                                                <p><strong><?php echo $L['health_notes'] ?? 'Health Notes'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['health_notes'])); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><?php echo $L['no_health_check_records'] ?? 'No health check records available.'; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Vaccination Modal -->
<div class="modal fade" id="addVaccinationModal" tabindex="-1" aria-labelledby="addVaccinationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="add-vaccination.php">
                <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addVaccinationModalLabel"><?php echo $L['add_vaccination'] ?? 'Add Vaccination'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vaccine_type" class="form-label"><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?></label>
                        <select class="form-select" id="vaccine_type" name="vaccine_type" required>
                            <option value=""><?php echo $L['select_vaccine_type'] ?? 'Select Vaccine Type'; ?></option>
                            <option value="rabies"><?php echo $L['rabies'] ?? 'Rabies'; ?></option>
                            <option value="distemper"><?php echo $L['distemper'] ?? 'Distemper'; ?></option>
                            <option value="parvovirus"><?php echo $L['parvovirus'] ?? 'Parvovirus'; ?></option>
                            <option value="other"><?php echo $L['other'] ?? 'Other'; ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="vaccine_name" class="form-label"><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?></label>
                        <input type="text" class="form-control" id="vaccine_name" name="vaccine_name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="batch_number" class="form-label"><?php echo $L['batch_number'] ?? 'Batch Number'; ?></label>
                            <input type="text" class="form-control" id="batch_number" name="batch_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="veterinarian" class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                            <input type="text" class="form-control" id="veterinarian" name="veterinarian">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vaccination_date" class="form-label"><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?></label>
                            <input type="date" class="form-control" id="vaccination_date" name="vaccination_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label"><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['close'] ?? 'Close'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $L['save'] ?? 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Vaccination Modal -->
<div class="modal fade" id="editVaccinationModal" tabindex="-1" aria-labelledby="editVaccinationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="edit-vaccination.php">
                <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editVaccinationModalLabel"><?php echo $L['edit_vaccination'] ?? 'Edit Vaccination'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editVaccinationModalBody">
                    <!-- Vaccination fields will be injected here by JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['close'] ?? 'Close'; ?></button>
                    <button type="submit" class="btn btn-warning"><?php echo $L['save_changes'] ?? 'Save Changes'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Health Check Modal -->
<div class="modal fade" id="addHealthCheckModal" tabindex="-1" aria-labelledby="addHealthCheckModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="add-health-check.php">
                <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addHealthCheckModalLabel"><?php echo $L['add_health_check'] ?? 'Add Health Check'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Sva polja kao u add-health-check.php -->
                    <div class="mb-3">
                        <label for="check_date" class="form-label"><?php echo $L['check_date'] ?? 'Check Date'; ?></label>
                        <input type="date" class="form-control" id="check_date" name="check_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="veterinarian" class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                        <input type="text" class="form-control" id="veterinarian" name="veterinarian">
                    </div>
                    <div class="mb-3">
                        <label for="health_status" class="form-label"><?php echo $L['health_status'] ?? 'Health Status'; ?></label>
                        <select class="form-select" id="health_status" name="health_status" required>
                            <option value=""><?php echo $L['select_health_status'] ?? 'Select Health Status'; ?></option>
                            <option value="excellent"><?php echo $L['excellent'] ?? 'Excellent'; ?></option>
                            <option value="good"><?php echo $L['good'] ?? 'Good'; ?></option>
                            <option value="fair"><?php echo $L['fair'] ?? 'Fair'; ?></option>
                            <option value="poor"><?php echo $L['poor'] ?? 'Poor'; ?></option>
                            <option value="critical"><?php echo $L['critical'] ?? 'Critical'; ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="diagnosis" class="form-label"><?php echo $L['diagnosis'] ?? 'Diagnosis'; ?></label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="treatment_plan" class="form-label"><?php echo $L['treatment_plan'] ?? 'Treatment Plan'; ?></label>
                        <textarea class="form-control" id="treatment_plan" name="treatment_plan"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="clinical_exam" class="form-label"><?php echo $L['clinical_exam'] ?? 'Clinical Exam'; ?></label>
                        <textarea class="form-control" id="clinical_exam" name="clinical_exam"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="animal_statement" class="form-label"><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?></label>
                        <textarea class="form-control" id="animal_statement" name="animal_statement"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="health_notes" class="form-label"><?php echo $L['health_notes'] ?? 'Health Notes'; ?></label>
                        <textarea class="form-control" id="health_notes" name="health_notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['close'] ?? 'Close'; ?></button>
                    <button type="submit" class="btn btn-success"><?php echo $L['save'] ?? 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Health Check Modal -->
<div class="modal fade" id="editHealthCheckModal" tabindex="-1" aria-labelledby="editHealthCheckModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="edit-health-check.php">
                <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editHealthCheckModalLabel"><?php echo $L['edit_health_check'] ?? 'Edit Health Check'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editHealthCheckModalBody">
                    <!-- Polja će biti ubačena JS-om -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['close'] ?? 'Close'; ?></button>
                    <button type="submit" class="btn btn-warning"><?php echo $L['save'] ?? 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editBtn = document.getElementById('editVaccinationBtn');
    var modalBody = document.getElementById('editVaccinationModalBody');
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            var vaccinations = JSON.parse(this.getAttribute('data-vaccinations'));
            var html = '';
            vaccinations.forEach(function(vac, idx) {
                html += `
                <div class="border rounded p-3 mb-3">
                    <input type="hidden" name="vaccination_id[${vac.id}]" value="${vac.id}">
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?></label>
                        <select class="form-select" name="vaccine_type[${vac.id}]">
                            <option value="rabies" ${vac.vaccine_type === 'rabies' ? 'selected' : ''}><?php echo $L['rabies'] ?? 'Rabies'; ?></option>
                            <option value="distemper" ${vac.vaccine_type === 'distemper' ? 'selected' : ''}><?php echo $L['distemper'] ?? 'Distemper'; ?></option>
                            <option value="parvovirus" ${vac.vaccine_type === 'parvovirus' ? 'selected' : ''}><?php echo $L['parvovirus'] ?? 'Parvovirus'; ?></option>
                            <option value="other" ${vac.vaccine_type === 'other' ? 'selected' : ''}><?php echo $L['other'] ?? 'Other'; ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?></label>
                        <input type="text" class="form-control" name="vaccine_name[${vac.id}]" value="${vac.vaccine_name ? vac.vaccine_name.replace(/"/g, '&quot;') : ''}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo $L['batch_number'] ?? 'Batch Number'; ?></label>
                            <input type="text" class="form-control" name="batch_number[${vac.id}]" value="${vac.batch_number ? vac.batch_number.replace(/"/g, '&quot;') : ''}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                            <input type="text" class="form-control" name="veterinarian[${vac.id}]" value="${vac.veterinarian ? vac.veterinarian.replace(/"/g, '&quot;') : ''}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?></label>
                            <input type="date" class="form-control" name="vaccination_date[${vac.id}]" value="${vac.vaccination_date || ''}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></label>
                            <input type="date" class="form-control" name="expiry_date[${vac.id}]" value="${vac.expiry_date || ''}">
                        </div>
                    </div>
                </div>
                `;
            });
            modalBody.innerHTML = html;
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var editBtn = document.getElementById('editHealthCheckBtn');
    var modalBody = document.getElementById('editHealthCheckModalBody');
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            var checks = JSON.parse(this.getAttribute('data-health-checks'));
            var html = '';
            checks.forEach(function(check, idx) {
                html += `
                <div class="border rounded p-3 mb-3">
                    <input type="hidden" name="health_check_id[${check.id}]" value="${check.id}">
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['check_date'] ?? 'Check Date'; ?></label>
                        <input type="date" class="form-control" name="check_date[${check.id}]" value="${check.check_date || ''}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                        <input type="text" class="form-control" name="veterinarian[${check.id}]" value="${check.veterinarian ? check.veterinarian.replace(/"/g, '&quot;') : ''}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['health_status'] ?? 'Health Status'; ?></label>
                        <select class="form-select" name="health_status[${check.id}]">
                            <option value="excellent" ${check.health_status === 'excellent' ? 'selected' : ''}><?php echo $L['excellent'] ?? 'Excellent'; ?></option>
                            <option value="good" ${check.health_status === 'good' ? 'selected' : ''}><?php echo $L['good'] ?? 'Good'; ?></option>
                            <option value="fair" ${check.health_status === 'fair' ? 'selected' : ''}><?php echo $L['fair'] ?? 'Fair'; ?></option>
                            <option value="poor" ${check.health_status === 'poor' ? 'selected' : ''}><?php echo $L['poor'] ?? 'Poor'; ?></option>
                            <option value="critical" ${check.health_status === 'critical' ? 'selected' : ''}><?php echo $L['critical'] ?? 'Critical'; ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['diagnosis'] ?? 'Diagnosis'; ?></label>
                        <textarea class="form-control" name="diagnosis[${check.id}]">${check.diagnosis ? check.diagnosis.replace(/</g, '&lt;') : ''}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['treatment_plan'] ?? 'Treatment Plan'; ?></label>
                        <textarea class="form-control" name="treatment_plan[${check.id}]">${check.treatment_plan ? check.treatment_plan.replace(/</g, '&lt;') : ''}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['clinical_exam'] ?? 'Clinical Exam'; ?></label>
                        <textarea class="form-control" name="clinical_exam[${check.id}]">${check.clinical_exam ? check.clinical_exam.replace(/</g, '&lt;') : ''}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?></label>
                        <textarea class="form-control" name="animal_statement[${check.id}]">${check.animal_statement ? check.animal_statement.replace(/</g, '&lt;') : ''}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['health_notes'] ?? 'Health Notes'; ?></label>
                        <textarea class="form-control" name="health_notes[${check.id}]">${check.health_notes ? check.health_notes.replace(/</g, '&lt;') : ''}</textarea>
                    </div>
                </div>
                `;
            });
            modalBody.innerHTML = html;
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>