<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();

if ($_SESSION['role'] === 'Volunteer') {
    header('Location: pets.php');
    exit();
}
// Proveri da li je ID ljubimca postavljen
if (!isset($_GET['id'])) {
    header('Location: pets.php');
    exit();
}

$pet_id = $_GET['id'];
$pet = getPetById($pet_id);

if (!$pet || ($pet['created_by'] != $_SESSION['user_id'] && !isAdmin())) {
    header('Location: pets.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $species = trim($_POST['species']);
    $breed = trim($_POST['breed']);
    $sex = $_POST['sex'];
    $birth_date = $_POST['birth_date'];
    $coat_color = trim($_POST['coat_color']);
    $coat_type = trim($_POST['coat_type']);
    $microchip_number = trim($_POST['microchip_number']);
    $microchip_date = $_POST['microchip_date'];
    $microchip_location = trim($_POST['microchip_location']);
    $status = $_POST['status'];
    $in_shelter = $_POST['in_shelter'];
    $incoming_date = $_POST['incoming_date'];
    
    // Obrada uploada slike
    $image_path = $pet['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/pets/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Obriši staru sliku ako postoji
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = $target_path;
        }
    }
    
    // Ažuriranje ljubimca u bazi
    $db->query(
        "UPDATE pets SET 
            name = ?, 
            species = ?, 
            breed = ?, 
            sex = ?, 
            birth_date = ?, 
            coat_color = ?, 
            coat_type = ?, 
            microchip_number = ?, 
            microchip_date = ?, 
            microchip_location = ?, 
            image_path = ?, 
            status = ?, 
            in_shelter = ?,
            incoming_date = ?
        WHERE id = ?",
        [$name, $species, $breed, $sex, $birth_date, $coat_color, $coat_type, 
         $microchip_number, $microchip_date, $microchip_location, $image_path, 
         $status, $in_shelter, $incoming_date, $pet_id]
    );
    
    // Update vaccinations
    if (!empty($_POST['vaccine_type'])) {
        foreach ($_POST['vaccine_type'] as $id => $type) {
            $db->query(
                "UPDATE vaccinations SET 
                    vaccine_type = ?, 
                    vaccine_name = ?, 
                    batch_number = ?, 
                    vaccination_date = ?, 
                    expiry_date = ?, 
                    veterinarian = ?
                 WHERE id = ?",
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
    
    $_SESSION['message'] = "Pet updated successfully!";
    header("Location: view-pet.php?id=$pet_id");
    exit();
}

// Fetch existing vaccinations
$vaccinations = $db->fetchAll("SELECT * FROM vaccinations WHERE pet_id = ?", [$pet_id]);

include 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Edit Pet: <?php echo htmlspecialchars($pet['name']); ?></h2>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Name*</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="species" class="form-label">Species*</label>
                    <select class="form-select" id="species" name="species" required>
                        <option value="dog" <?php echo $pet['species'] === 'dog' ? 'selected' : ''; ?>>Dog</option>
                        <option value="cat" <?php echo $pet['species'] === 'cat' ? 'selected' : ''; ?>>Cat</option>
                        <option value="rabbit" <?php echo $pet['species'] === 'rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                        <option value="bird" <?php echo $pet['species'] === 'bird' ? 'selected' : ''; ?>>Bird</option>
                        <option value="other" <?php echo $pet['species'] === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="breed" class="form-label">Breed*</label>
                    <input type="text" class="form-control" id="breed" name="breed" value="<?php echo htmlspecialchars($pet['breed']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="sex" class="form-label">Sex*</label>
                    <select class="form-select" id="sex" name="sex" required>
                        <option value="male" <?php echo $pet['sex'] === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo $pet['sex'] === 'female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="birth_date" class="form-label">Birth Date</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($pet['birth_date']); ?>">
                </div>
                <div class="mb-3">
                    <label for="coat_color" class="form-label">Coat Color</label>
                    <input type="text" class="form-control" id="coat_color" name="coat_color" value="<?php echo htmlspecialchars($pet['coat_color']); ?>">
                </div>
                <div class="mb-3">
                    <label for="coat_type" class="form-label">Coat Type</label>
                    <input type="text" class="form-control" id="coat_type" name="coat_type" value="<?php echo htmlspecialchars($pet['coat_type']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="microchip_number" class="form-label">Microchip Number</label>
                    <input type="text" class="form-control" id="microchip_number" name="microchip_number" value="<?php echo htmlspecialchars($pet['microchip_number']); ?>">
                </div>
                <div class="mb-3">
                    <label for="microchip_date" class="form-label">Microchip Date</label>
                    <input type="date" class="form-control" id="microchip_date" name="microchip_date" value="<?php echo htmlspecialchars($pet['microchip_date']); ?>">
                </div>
                <div class="mb-3">
                    <label for="microchip_location" class="form-label">Microchip Location</label>
                    <input type="text" class="form-control" id="microchip_location" name="microchip_location" value="<?php echo htmlspecialchars($pet['microchip_location']); ?>">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status*</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="available" <?php echo $pet['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="adopted" <?php echo $pet['status'] === 'adopted' ? 'selected' : ''; ?>>Adopted</option>
                        <option value="fostered" <?php echo $pet['status'] === 'fostered' ? 'selected' : ''; ?>>Fostered</option>
                        <option value="medical" <?php echo $pet['status'] === 'medical' ? 'selected' : ''; ?>>Medical</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="in_shelter" class="form-label">Presence in Shelter</label>
                    <select class="form-select" id="in_shelter" name="in_shelter" required>
                        <option value="1" <?php echo $pet['in_shelter'] ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo !$pet['in_shelter'] ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="incoming_date" class="form-label">Incoming Date</label>
                    <input type="date" class="form-control" id="incoming_date" name="incoming_date" value="<?php echo htmlspecialchars($pet['incoming_date']); ?>">
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <?php if ($pet['image_path']): ?>
                        <div class="mt-2">
                            <a href="<?php echo htmlspecialchars($pet['image_path']); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" alt="Current pet image" class="img-thumbnail" style="max-height: 150px;">
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Update Pet</button>
            <a href="view-pet.php?id=<?php echo $pet_id; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <h4 class="mt-4">Vaccinations</h4>
    <div id="vaccination-container">
        <?php foreach ($vaccinations as $index => $vac): ?>
            <div class="vaccination-entry mb-3 border p-3">
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
    </div>
</div>

<?php include 'includes/footer.php'; ?>