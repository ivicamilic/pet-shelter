<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

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

include 'includes/header.php';
?>

<div class="container mt-4">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pet Details: <?php echo htmlspecialchars($pet['name']); ?></h2>
        <a href="pets.php" class="btn btn-secondary">Back to All Pets</a>
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
                        <p>No image available</p>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($pet['name']); ?></h5>
                    <p class="card-text">
                        <strong>Status:</strong> 
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
                            <?php echo ucfirst($pet['status']); ?>
                        </span>
                    </p>
                    <p class="card-text">
                        <strong>Presence in Shelter:</strong>
                        <?php echo $pet['in_shelter'] ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>'; ?>
                    </p>
                    <p class="card-text">
                        <strong>Incoming Date:</strong>
                        <?php echo !empty($pet['incoming_date']) ? date('M j, Y', strtotime($pet['incoming_date'])) : '<span class="text-muted">N/A</span>'; ?>
                    </p>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Species:</strong> <?php echo ucfirst(htmlspecialchars($pet['species'])); ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Sex:</strong> <?php echo ucfirst(htmlspecialchars($pet['sex'])); ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Date of Birth:</strong> 
                        <?php echo $pet['birth_date'] ? date('M j, Y', strtotime($pet['birth_date'])) : 'Unknown'; ?>
                    </li>
                </ul>
                <div class="card-body">
                    <?php if ($_SESSION['user_id'] == $pet['created_by'] || isAdmin()): ?>
                        <a href="edit-pet.php?id=<?php echo $pet['id']; ?>" class="card-link btn btn-warning">Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Identification</h5>
                </div>
                <div class="card-body">
                    <p><strong>Microchip Number:</strong> <?php echo htmlspecialchars($pet['microchip_number'] ?: 'Not available'); ?></p>
                    <p><strong>Microchip Date:</strong> <?php echo $pet['microchip_date'] ? date('M j, Y', strtotime($pet['microchip_date'])) : 'Not available'; ?></p>
                    <p><strong>Microchip Location:</strong> <?php echo htmlspecialchars($pet['microchip_location'] ?: 'Not available'); ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Vaccinations</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVaccinationModal">
                        Add Vaccination
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($pet['vaccinations'])): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Name</th>
                                        <th>Date</th>
                                        <th>Expiry</th>
                                        <th>Veterinarian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pet['vaccinations'] as $vaccination): ?>
                                        <tr>
                                            <td><?php echo ucfirst(htmlspecialchars($vaccination['vaccine_type'])); ?></td>
                                            <td><?php echo htmlspecialchars($vaccination['vaccine_name']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($vaccination['vaccination_date'])); ?></td>
                                            <td><?php echo $vaccination['expiry_date'] ? date('M j, Y', strtotime($vaccination['expiry_date'])) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($vaccination['veterinarian'] ?: 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No vaccination records available.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Health Checks</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($pet['health_checks'])): ?>
                        <div class="accordion" id="healthChecksAccordion">
                            <?php foreach ($pet['health_checks'] as $index => $check): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                            <?php echo date('M j, Y', strtotime($check['check_date'])); ?> - 
                                            <span class="ms-2 badge 
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
                                                <?php echo ucfirst($check['health_status']); ?>
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#healthChecksAccordion">
                                        <div class="accordion-body">
                                            <p><strong>Veterinarian:</strong> <?php echo htmlspecialchars($check['veterinarian']); ?></p>
                                            <p><strong>Diagnosis:</strong> <?php echo nl2br(htmlspecialchars($check['diagnosis'] ?: 'N/A')); ?></p>
                                            <p><strong>Treatment Plan:</strong> <?php echo nl2br(htmlspecialchars($check['treatment_plan'] ?: 'N/A')); ?></p>
                                            <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($check['notes'] ?: 'N/A')); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No health check records available.</p>
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
                    <h5 class="modal-title" id="addVaccinationModalLabel">Add Vaccination Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vaccine_type" class="form-label">Vaccine Type</label>
                        <select class="form-select" id="vaccine_type" name="vaccine_type" required>
                            <option value="">Select vaccine type</option>
                            <option value="rabies">Rabies</option>
                            <option value="distemper">Distemper</option>
                            <option value="parvovirus">Parvovirus</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="vaccine_name" class="form-label">Vaccine Name</label>
                        <input type="text" class="form-control" id="vaccine_name" name="vaccine_name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="batch_number" class="form-label">Batch Number</label>
                            <input type="text" class="form-control" id="batch_number" name="batch_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="veterinarian" class="form-label">Veterinarian</label>
                            <input type="text" class="form-control" id="veterinarian" name="veterinarian">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vaccination_date" class="form-label">Vaccination Date</label>
                            <input type="date" class="form-control" id="vaccination_date" name="vaccination_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Vaccination</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>