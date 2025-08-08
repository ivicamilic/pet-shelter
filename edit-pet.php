<?php
// filepath: c:\xampp\htdocs\pet-shelter\edit-pet.php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();

if ($_SESSION['role'] === 'Volunteer') {
    header('Location: pets.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: pets.php');
    exit();
}

$pet_id = (int)$_GET['id'];
$pet = getPetById($pet_id);

if (!$pet || ($pet['created_by'] != $_SESSION['user_id'] && !isAdmin())) {
    header('Location: pets.php');
    exit();
}

$vaccinations = $db->fetchAll("SELECT * FROM vaccinations WHERE pet_id = ?", [$pet_id]);
$health_check = $db->fetchOne("SELECT * FROM health_checks WHERE pet_id = ?", [$pet_id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PET UPDATE
    $db->query(
        "UPDATE pets SET 
            name=?, species=?, breed=?, sex=?, birth_date=?, coat_color=?, coat_type=?, 
            microchip_number=?, microchip_date=?, microchip_location=?, image_path=?, 
            status=?, in_shelter=?, incoming_date=?
         WHERE id=?",
        [
            trim($_POST['name']),
            trim($_POST['species']),
            trim($_POST['breed']),
            $_POST['sex'],
            $_POST['birth_date'],
            trim($_POST['coat_color']),
            trim($_POST['coat_type']),
            trim($_POST['microchip_number']),
            $_POST['microchip_date'],
            trim($_POST['microchip_location']),
            $pet['image_path'], // Ostavlja staru sliku, dodaš upload ako treba
            $_POST['status'],
            $_POST['in_shelter'],
            $_POST['incoming_date'],
            $pet_id
        ]
    );

    // VACCINATIONS UPDATE
    if (!empty($_POST['vaccine_type'])) {
        foreach ($_POST['vaccine_type'] as $id => $type) {
            $db->query(
                "UPDATE vaccinations SET 
                    vaccine_type=?, vaccine_name=?, batch_number=?, vaccination_date=?, expiry_date=?, veterinarian=?
                 WHERE id=?",
                [
                    $type,
                    $_POST['vaccine_name'][$id],
                    $_POST['batch_number'][$id],
                    $_POST['vaccination_date'][$id],
                    $_POST['expiry_date'][$id],
                    $_POST['veterinarian'][$id],
                    $id
                ]
            );
        }
    }

    // HEALTH CHECK UPDATE/INSERT
    $exists = $db->fetchOne("SELECT id FROM health_checks WHERE pet_id = ?", [$pet_id]);
    $fields = [
        $_POST['check_date'],
        $_POST['veterinarian_hc'],
        $_POST['health_status'],
        $_POST['diagnosis'],
        $_POST['treatment_plan'],
        $_POST['clinical_exam'],
        $_POST['animal_statement'],
        $_POST['health_notes'],
        $pet_id
    ];
    if ($exists) {
        $db->query(
            "UPDATE health_checks SET 
                check_date=?, veterinarian=?, health_status=?, diagnosis=?, treatment_plan=?, 
                clinical_exam=?, animal_statement=?, health_notes=?
             WHERE pet_id=?",
            $fields
        );
    } else {
        $db->query(
            "INSERT INTO health_checks 
                (check_date, veterinarian, health_status, diagnosis, treatment_plan, clinical_exam, animal_statement, health_notes, pet_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            $fields
        );
    }

    $_SESSION['message'] = "Pet and related data updated successfully!";
    header("Location: view-pet.php?id=$pet_id");
    exit();
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Edit Pet: <?php echo htmlspecialchars($pet['name']); ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- PET BASIC INFO -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Name*</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Species*</label>
                    <select class="form-select" name="species" required>
                        <option value="dog" <?php if($pet['species']=='dog') echo 'selected'; ?>>Dog</option>
                        <option value="cat" <?php if($pet['species']=='cat') echo 'selected'; ?>>Cat</option>
                        <option value="rabbit" <?php if($pet['species']=='rabbit') echo 'selected'; ?>>Rabbit</option>
                        <option value="bird" <?php if($pet['species']=='bird') echo 'selected'; ?>>Bird</option>
                        <option value="other" <?php if($pet['species']=='other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Breed*</label>
                    <input type="text" class="form-control" name="breed" value="<?php echo htmlspecialchars($pet['breed']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sex*</label>
                    <select class="form-select" name="sex" required>
                        <option value="male" <?php if($pet['sex']=='male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if($pet['sex']=='female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Birth Date</label>
                    <input type="date" class="form-control" name="birth_date" value="<?php echo htmlspecialchars($pet['birth_date']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Coat Color</label>
                    <input type="text" class="form-control" name="coat_color" value="<?php echo htmlspecialchars($pet['coat_color']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Coat Type</label>
                    <input type="text" class="form-control" name="coat_type" value="<?php echo htmlspecialchars($pet['coat_type']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Microchip Number</label>
                    <input type="text" class="form-control" name="microchip_number" value="<?php echo htmlspecialchars($pet['microchip_number']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Microchip Date</label>
                    <input type="date" class="form-control" name="microchip_date" value="<?php echo htmlspecialchars($pet['microchip_date']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Microchip Location</label>
                    <input type="text" class="form-control" name="microchip_location" value="<?php echo htmlspecialchars($pet['microchip_location']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status*</label>
                    <select class="form-select" name="status" required>
                        <option value="available" <?php if($pet['status']=='available') echo 'selected'; ?>>Available</option>
                        <option value="adopted" <?php if($pet['status']=='adopted') echo 'selected'; ?>>Adopted</option>
                        <option value="fostered" <?php if($pet['status']=='fostered') echo 'selected'; ?>>Fostered</option>
                        <option value="medical" <?php if($pet['status']=='medical') echo 'selected'; ?>>Medical</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Presence in Shelter</label>
                    <select class="form-select" name="in_shelter" required>
                        <option value="1" <?php if($pet['in_shelter']) echo 'selected'; ?>>Yes</option>
                        <option value="0" <?php if(!$pet['in_shelter']) echo 'selected'; ?>>No</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Incoming Date</label>
                    <input type="date" class="form-control" name="incoming_date" value="<?php echo htmlspecialchars($pet['incoming_date']); ?>">
                </div>
                <!-- Image upload can be added here if needed -->
            </div>
        </div>
        <hr>
        <!-- VACCINATIONS -->
        <h4>Vaccinations</h4>
        <?php foreach ($vaccinations as $vac): ?>
            <div class="border p-3 mb-3">
                <input type="hidden" name="vaccination_id[<?php echo $vac['id']; ?>]" value="<?php echo $vac['id']; ?>">
                <div class="mb-3">
                    <label class="form-label">Vaccine Type</label>
                    <select class="form-select" name="vaccine_type[<?php echo $vac['id']; ?>]">
                        <option value="rabies" <?php if($vac['vaccine_type']=='rabies') echo 'selected'; ?>>Rabies</option>
                        <option value="distemper" <?php if($vac['vaccine_type']=='distemper') echo 'selected'; ?>>Distemper</option>
                        <option value="parvovirus" <?php if($vac['vaccine_type']=='parvovirus') echo 'selected'; ?>>Parvovirus</option>
                        <option value="other" <?php if($vac['vaccine_type']=='other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Vaccine Name</label>
                    <input type="text" class="form-control" name="vaccine_name[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['vaccine_name']); ?>">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Batch Number</label>
                        <input type="text" class="form-control" name="batch_number[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['batch_number']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Veterinarian</label>
                        <input type="text" class="form-control" name="veterinarian[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['veterinarian']); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Vaccination Date</label>
                        <input type="date" class="form-control" name="vaccination_date[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['vaccination_date']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['expiry_date']); ?>">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <hr>
        <!-- HEALTH CHECK -->
        <h4>Health Check</h4>
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Check Date</label>
                    <input type="date" class="form-control" name="check_date" value="<?php echo htmlspecialchars($health_check['check_date'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Veterinarian</label>
                    <input type="text" class="form-control" name="veterinarian_hc" value="<?php echo htmlspecialchars($health_check['veterinarian'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Health Status</label>
                    <select class="form-select" name="health_status">
                        <option value="">Select status</option>
                        <option value="excellent" <?php if(($health_check['health_status'] ?? '') === 'excellent') echo 'selected'; ?>>Excellent</option>
                        <option value="good" <?php if(($health_check['health_status'] ?? '') === 'good') echo 'selected'; ?>>Good</option>
                        <option value="fair" <?php if(($health_check['health_status'] ?? '') === 'fair') echo 'selected'; ?>>Fair</option>
                        <option value="poor" <?php if(($health_check['health_status'] ?? '') === 'poor') echo 'selected'; ?>>Poor</option>
                        <option value="critical" <?php if(($health_check['health_status'] ?? '') === 'critical') echo 'selected'; ?>>Critical</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Diagnosis</label>
                    <textarea class="form-control" name="diagnosis"><?php echo htmlspecialchars($health_check['diagnosis'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Treatment Plan</label>
                    <textarea class="form-control" name="treatment_plan"><?php echo htmlspecialchars($health_check['treatment_plan'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Clinical Exam</label>
                    <textarea class="form-control" name="clinical_exam"><?php echo htmlspecialchars($health_check['clinical_exam'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Animal Statement</label>
                    <textarea class="form-control" name="animal_statement"><?php echo htmlspecialchars($health_check['animal_statement'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Additional Notes</label>
                    <textarea class="form-control" name="health_notes"><?php echo htmlspecialchars($health_check['health_notes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update Pet</button>
        <a href="view-pet.php?id=<?php echo $pet_id; ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include 'includes/footer.php'; ?>