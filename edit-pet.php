<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$lang = $_SESSION['lang'] ?? 'en';
$L = require __DIR__ . '/lang/' . $lang . '.php';

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

// Dozvola pristupa edit stranici admin i staff mogu menjati sve ljubimce
// obican korisnik moze samo one koje je sam kreirao

if (
    !$pet ||
    (!isAdminOrStaff() && $pet['created_by'] != $_SESSION['user_id'])
) {
    header('Location: pets.php');
    exit();
}

$vaccinations = $db->fetchAll("SELECT * FROM vaccinations WHERE pet_id = ?", [$pet_id]);
$health_check = $db->fetchOne("SELECT * FROM health_checks WHERE pet_id = ?", [$pet_id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PET UPDATE
    // Obrada uploada nove slike
    $image_path = $pet['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/pets/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            // Brisanje stare slike, ako postoji
            if (!empty($pet['image_path']) && file_exists($pet['image_path'])) {
                unlink($pet['image_path']);
            }
            $image_path = $target_path;
        }
    }

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
            $image_path,
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

    // NEW VACCINATION INSERT
    if (!empty($_POST['new_vaccine_type'])) {
        $db->query(
            "INSERT INTO vaccinations 
                (pet_id, vaccine_type, vaccine_name, batch_number, vaccination_date, expiry_date, veterinarian)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $pet_id,
                $_POST['new_vaccine_type'],
                $_POST['new_vaccine_name'],
                $_POST['new_batch_number'],
                $_POST['new_vaccination_date'],
                $_POST['new_expiry_date'],
                $_POST['new_veterinarian']
            ]
        );
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
    <h2><?php echo $L['edit'] ?? 'Edit'; ?>: <?php echo htmlspecialchars($pet['name']); ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['name'] ?? 'Name'; ?>*</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['species'] ?? 'Species'; ?>*</label>
                    <select class="form-select" name="species" required>
                        <option value="dog" <?php if($pet['species']=='dog') echo 'selected'; ?>><?php echo $L['dog'] ?? 'Dog'; ?></option>
                        <option value="cat" <?php if($pet['species']=='cat') echo 'selected'; ?>><?php echo $L['cat'] ?? 'Cat'; ?></option>
                        <option value="rabbit" <?php if($pet['species']=='rabbit') echo 'selected'; ?>><?php echo $L['rabbit'] ?? 'Rabbit'; ?></option>
                        <option value="bird" <?php if($pet['species']=='bird') echo 'selected'; ?>><?php echo $L['bird'] ?? 'Bird'; ?></option>
                        <option value="other" <?php if($pet['species']=='other') echo 'selected'; ?>><?php echo $L['other'] ?? 'Other'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['breed'] ?? 'Breed'; ?>*</label>
                    <input type="text" class="form-control" name="breed" value="<?php echo htmlspecialchars($pet['breed']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['sex'] ?? 'Sex'; ?>*</label>
                    <select class="form-select" name="sex" required>
                        <option value="male" <?php if($pet['sex']=='male') echo 'selected'; ?>><?php echo $L['male'] ?? 'Male'; ?></option>
                        <option value="female" <?php if($pet['sex']=='female') echo 'selected'; ?>><?php echo $L['female'] ?? 'Female'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['date_of_birth'] ?? 'Date of Birth'; ?></label>
                    <input type="date" class="form-control" name="birth_date" value="<?php echo htmlspecialchars($pet['birth_date']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['coat_color'] ?? 'Coat Color'; ?></label>
                    <input type="text" class="form-control" name="coat_color" value="<?php echo htmlspecialchars($pet['coat_color']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['coat_type'] ?? 'Coat Type'; ?></label>
                    <input type="text" class="form-control" name="coat_type" value="<?php echo htmlspecialchars($pet['coat_type']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['microchip_number'] ?? 'Microchip Number'; ?></label>
                    <input type="text" class="form-control" name="microchip_number" value="<?php echo htmlspecialchars($pet['microchip_number']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['microchip_date'] ?? 'Microchip Date'; ?></label>
                    <input type="date" class="form-control" name="microchip_date" value="<?php echo htmlspecialchars($pet['microchip_date']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['microchip_location'] ?? 'Microchip Location'; ?></label>
                    <input type="text" class="form-control" name="microchip_location" value="<?php echo htmlspecialchars($pet['microchip_location']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['status'] ?? 'Status'; ?>*</label>
                    <select class="form-select" name="status" required>
                        <option value="available" <?php if($pet['status']=='available') echo 'selected'; ?>><?php echo $L['available'] ?? 'Available'; ?></option>
                        <option value="adopted" <?php if($pet['status']=='adopted') echo 'selected'; ?>><?php echo $L['adopted'] ?? 'Adopted'; ?></option>
                        <option value="fostered" <?php if($pet['status']=='fostered') echo 'selected'; ?>><?php echo $L['fostered'] ?? 'Fostered'; ?></option>
                        <option value="medical" <?php if($pet['status']=='medical') echo 'selected'; ?>><?php echo $L['medical'] ?? 'Medical'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['presence_in_shelter'] ?? ' Presence In Shelter'; ?></label>
                    <select class="form-select" name="in_shelter" required>
                        <option value="1" <?php if($pet['in_shelter']) echo 'selected'; ?>><?php echo $L['yes'] ?? 'Yes'; ?></option>
                        <option value="0" <?php if(!$pet['in_shelter']) echo 'selected'; ?>><?php echo $L['no'] ?? 'No'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['incoming_date'] ?? 'Incoming Date'; ?></label>
                    <input type="date" class="form-control" name="incoming_date" value="<?php echo htmlspecialchars($pet['incoming_date']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['current_image'] ?? 'Current Image'; ?></label>
                    <div>
                        <?php if (!empty($pet['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                        <?php else: ?>
                            <p><?php echo $L['no_image_uploaded'] ?? 'No image uploaded.'; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="file-upload-button">
                        <?php echo $L['change_image'] ?? 'Change Image'; ?>
                    </label>
                    <div class="input-group mb-3">
                        <input type="file" class="form-control d-none" id="image-upload" name="image" accept="image/*">
                        <button class="btn btn-outline-secondary" type="button" id="file-upload-button">
                            <?php echo $L['choose_file'] ?? 'Choose File'; ?>
                        </button>
                        <input type="text" class="form-control" id="file-name" readonly placeholder="<?php echo $L['no_file_chosen'] ?? 'Nije izabran nijedan fajl'; ?>">
                    </div>                
                </div>
            </div>
        </div>
        <hr>
        <!-- VACCINATIONS -->
        <h4><?php echo $L['vaccinations'] ?? 'Vaccinations'; ?></h4>
        <?php foreach ($vaccinations as $vac): ?>
            <div class="border p-3 mb-3">
                <input type="hidden" name="vaccination_id[<?php echo $vac['id']; ?>]" value="<?php echo $vac['id']; ?>">
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?></label>
                    <select class="form-select" name="vaccine_type[<?php echo $vac['id']; ?>]">
                        <option value="rabies" <?php if($vac['vaccine_type']=='rabies') echo 'selected'; ?>><?php echo $L['rabies'] ?? 'Rabies'; ?></option>
                        <option value="distemper" <?php if($vac['vaccine_type']=='distemper') echo 'selected'; ?>><?php echo $L['distemper'] ?? 'Distemper'; ?></option>
                        <option value="parvovirus" <?php if($vac['vaccine_type']=='parvovirus') echo 'selected'; ?>><?php echo $L['parvovirus'] ?? 'Parvovirus'; ?></option>
                        <option value="other" <?php if($vac['vaccine_type']=='other') echo 'selected'; ?>><?php echo $L['other'] ?? 'Other'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?></label>
                    <input type="text" class="form-control" name="vaccine_name[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['vaccine_name']); ?>">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?php echo $L['batch_number'] ?? 'Batch Number'; ?></label>
                        <input type="text" class="form-control" name="batch_number[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['batch_number']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                        <input type="text" class="form-control" name="veterinarian[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['veterinarian']); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?></label>
                        <input type="date" class="form-control" name="vaccination_date[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['vaccination_date']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></label>
                        <input type="date" class="form-control" name="expiry_date[<?php echo $vac['id']; ?>]" value="<?php echo htmlspecialchars($vac['expiry_date']); ?>">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($vaccinations)): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?></label>
                        <select class="form-select" name="new_vaccine_type">
                            <option value=""><?php echo $L['select_vaccine_type'] ?? 'Select Vaccine Type'; ?></option>
                            <option value="rabies"><?php echo $L['rabies'] ?? 'Rabies'; ?></option>
                            <option value="distemper"><?php echo $L['distemper'] ?? 'Distemper'; ?></option>
                            <option value="parvovirus"><?php echo $L['parvovirus'] ?? 'Parvovirus'; ?></option>
                            <option value="other"><?php echo $L['other'] ?? 'Other'; ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?></label>
                        <input type="text" class="form-control" name="new_vaccine_name">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo $L['batch_number'] ?? 'Batch Number'; ?></label>
                            <input type="text" class="form-control" name="new_batch_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                            <input type="text" class="form-control" name="new_veterinarian">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?></label>
                            <input type="date" class="form-control" name="new_vaccination_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></label>
                            <input type="date" class="form-control" name="new_expiry_date">
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <hr>
        <!-- HEALTH CHECK -->
        <h4><?php echo $L['health_checks'] ?? 'Health Checks'; ?></h4>
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['check_date'] ?? 'Check Date'; ?></label>
                    <input type="date" class="form-control" name="check_date" value="<?php echo htmlspecialchars($health_check['check_date'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                    <input type="text" class="form-control" name="veterinarian_hc" value="<?php echo htmlspecialchars($health_check['veterinarian'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['health_status'] ?? 'Health Status'; ?></label>
                    <select class="form-select" name="health_status">
                        <option value=""><?php echo $L['select_health_status'] ?? 'Select Health Status'; ?></option>
                        <option value="excellent" <?php if(($health_check['health_status'] ?? '') === 'excellent') echo 'selected'; ?>><?php echo $L['excellent'] ?? 'Excellent'; ?></option>
                        <option value="good" <?php if(($health_check['health_status'] ?? '') === 'good') echo 'selected'; ?>><?php echo $L['good'] ?? 'Good'; ?></option>
                        <option value="fair" <?php if(($health_check['health_status'] ?? '') === 'fair') echo 'selected'; ?>><?php echo $L['fair'] ?? 'Fair'; ?></option>
                        <option value="poor" <?php if(($health_check['health_status'] ?? '') === 'poor') echo 'selected'; ?>><?php echo $L['poor'] ?? 'Poor'; ?></option>
                        <option value="critical" <?php if(($health_check['health_status'] ?? '') === 'critical') echo 'selected'; ?>><?php echo $L['critical'] ?? 'Critical'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['diagnosis'] ?? 'Diagnosis'; ?></label>
                    <textarea class="form-control" name="diagnosis"><?php echo htmlspecialchars($health_check['diagnosis'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['treatment_plan'] ?? 'Treatment Plan'; ?></label>
                    <textarea class="form-control" name="treatment_plan"><?php echo htmlspecialchars($health_check['treatment_plan'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['clinical_exam'] ?? 'Clinical Exam'; ?></label>
                    <textarea class="form-control" name="clinical_exam"><?php echo htmlspecialchars($health_check['clinical_exam'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?></label>
                    <textarea class="form-control" name="animal_statement"><?php echo htmlspecialchars($health_check['animal_statement'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['health_notes'] ?? 'Health Notes'; ?></label>
                    <textarea class="form-control" name="health_notes"><?php echo htmlspecialchars($health_check['health_notes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $L['save'] ?? 'Save'; ?></button>
        <a href="view-pet.php?id=<?php echo $pet_id; ?>" class="btn btn-secondary"><?php echo $L['cancel'] ?? 'Cancel'; ?></a>
    </form>
</div>

<script>
document.getElementById('file-upload-button').addEventListener('click', function() {
    document.getElementById('image-upload').click();
});

document.getElementById('image-upload').addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : '';
    document.getElementById('file-name').value = fileName;
});
</script>

<?php include 'includes/footer.php'; ?>