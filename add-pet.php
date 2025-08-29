<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

$lang = $_SESSION['lang'] ?? 'en';
$L = require __DIR__ . '/lang/' . $lang . '.php';

redirectIfNotLoggedIn();

if ($_SESSION['role'] === 'Volunteer') {
    header('Location: pets.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $species = trim($_POST['species']);
    $breed = trim($_POST['breed']);
    $sex = $_POST['sex'];
    $birth_date = $_POST['birth_date'];
    $incoming_date = $_POST['incoming_date'];
    $coat_color = trim($_POST['coat_color']);
    $coat_type = trim($_POST['coat_type']);
    $microchip_number = trim($_POST['microchip_number']);
    $microchip_date = $_POST['microchip_date'];
    $microchip_location = trim($_POST['microchip_location']);
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id'];
    
    // Obrada uploada slike
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/pets/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = $target_path;
        }
    }
    
    // Unos ljubimca u bazu
    // Izmenjen upit u fajlu add-pet.php
    $db->query(
        "INSERT INTO `pets` (`name`, `species`, `breed`, `sex`, `birth_date`, `coat_color`, `coat_type`, `microchip_number`, `microchip_date`, `microchip_location`, `image_path`, `status`, `incoming_date`, `created_by`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$name, $species, $breed, $sex, $birth_date, $coat_color, $coat_type, $microchip_number, $microchip_date, $microchip_location, $image_path, $status, $incoming_date, $user_id]
    );
    
    $pet_id = $db->getConnection()->insert_id;
    
    // Unos vakcinacija ako postoje
    if (!empty($_POST['vaccine_type'])) {
        foreach ($_POST['vaccine_type'] as $index => $vaccine_type) {
            if (!empty($vaccine_type)) {
                // Izmenjen upit za vakcinacije
                $db->query(
                    "INSERT INTO `vaccinations` (`pet_id`, `vaccine_type`, `vaccine_name`, `batch_number`, `vaccination_date`, `expiry_date`, `veterinarian`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$pet_id, $vaccine_type, $_POST['vaccine_name'][$index], $_POST['batch_number'][$index], 
                    $_POST['vaccination_date'][$index], $_POST['expiry_date'][$index], $_POST['veterinarian'][$index]]
                );
            }
        }
    }
    
    // Unos zdravstvenih pregleda
    // Izmenjen upit za zdravstvene preglede
    $db->query(
        "INSERT INTO `health_checks` (`pet_id`, `clinical_exam`, `animal_statement`, `condition`, `health_notes`) VALUES (?, ?, ?, ?, ?)",
        [
            $pet_id,
            $_POST['clinical_exam'],
            $_POST['animal_statement'],
            $_POST['condition'],
            $_POST['health_notes']
        ]
    );
    
    $_SESSION['message'] = "Pet added successfully!";
    header("Location: view-pet.php?id=$pet_id");
    exit();
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <h2><?php echo $L['add_pet'] ?? 'Add Pet'; ?></h2>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <h4><?php echo $L['basic_information'] ?? 'Basic Information'; ?></h4>
                <div class="mb-3">
                    <label for="name" class="form-label"><?php echo $L['name'] ?? 'Name'; ?>*</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="species" class="form-label"><?php echo $L['species'] ?? 'Species'; ?>*</label>
                    <select class="form-select" id="species" name="species" required>
                        <option value=""><?php echo $L['select_species'] ?? 'Select species'; ?></option>
                        <option value="dog"><?php echo $L['dog'] ?? 'Dog'; ?></option>
                        <option value="cat"><?php echo $L['cat'] ?? 'Cat'; ?></option>
                        <option value="rabbit"><?php echo $L['rabbit'] ?? 'Rabbit'; ?></option>
                        <option value="bird"><?php echo $L['bird'] ?? 'Bird'; ?></option>
                        <option value="other"><?php echo $L['other'] ?? 'Other'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="breed" class="form-label"><?php echo $L['breed'] ?? 'Breed'; ?></label>
                    <input type="text" class="form-control" id="breed" name="breed">
                </div>
                <div class="mb-3">
                    <label for="sex" class="form-label"><?php echo $L['sex'] ?? 'Sex'; ?></label>
                    <select class="form-select" id="sex" name="sex">
                        <option value="unknown"><?php echo $L['unknown'] ?? 'Unknown'; ?></option>
                        <option value="male"><?php echo $L['male'] ?? 'Male'; ?></option>
                        <option value="female"><?php echo $L['female'] ?? 'Female'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="birth_date" class="form-label"><?php echo $L['date_of_birth'] ?? 'Date of Birth'; ?></label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date">
                </div>
                <div class="mb-3">
                    <label for="incoming_date" class="form-label"><?php echo $L['incoming_date'] ?? 'Incoming Date'; ?></label>
                    <input type="date" class="form-control" id="incoming_date" name="incoming_date">
                </div>
                <div class="mb-3">
                    <label for="coat_color" class="form-label"><?php echo $L['coat_color'] ?? 'Coat Color'; ?></label>
                    <input type="text" class="form-control" id="coat_color" name="coat_color">
                </div>
                <div class="mb-3">
                    <label for="coat_type" class="form-label"><?php echo $L['coat_type'] ?? 'Coat Type'; ?></label>
                    <input type="text" class="form-control" id="coat_type" name="coat_type">
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label"><?php echo $L['image'] ?? 'Image'; ?></label>
                    <div class="input-group mb-3">
                        <input type="file" class="form-control d-none" id="image-upload" name="image" accept="image/*">
                        <button class="btn btn-outline-secondary" type="button" id="file-upload-button">
                            <?php echo $L['choose_file'] ?? 'Choose File'; ?>
                        </button>
                        <input type="text" class="form-control" id="file-name" readonly placeholder="<?php echo $L['no_file_chosen'] ?? 'Nije izabran nijedan fajl'; ?>">
                    </div>                
                </div>
            </div>
            
            <div class="col-md-6">
                <h4><?php echo $L['microchip_information'] ?? 'Microchip Information'; ?></h4>
                <div class="mb-3">
                    <label for="microchip_number" class="form-label"><?php echo $L['microchip_number'] ?? 'Microchip Number'; ?></label>
                    <input type="text" class="form-control" id="microchip_number" name="microchip_number">
                </div>
                <div class="mb-3">
                    <label for="microchip_date" class="form-label"><?php echo $L['microchip_date'] ?? 'Microchip Date'; ?></label>
                    <input type="date" class="form-control" id="microchip_date" name="microchip_date">
                </div>
                <div class="mb-3">
                    <label for="microchip_location" class="form-label"><?php echo $L['microchip_location'] ?? 'Microchip Location'; ?></label>
                    <input type="text" class="form-control" id="microchip_location" name="microchip_location">
                </div>
                
                <h4 class="mt-4"><?php echo $L['status'] ?? 'Status'; ?></h4>
                <div class="mb-3">
                    <label for="status" class="form-label"><?php echo $L['status'] ?? 'Status'; ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value="available"><?php echo $L['available_for_adoption'] ?? 'Available for adoption'; ?></option>
                        <option value="adopted"><?php echo $L['adopted'] ?? 'Adopted'; ?></option>
                        <option value="fostered"><?php echo $L['fostered'] ?? 'Fostered'; ?></option>
                        <option value="medical"><?php echo $L['under_medical_care'] ?? 'Under Medical care'; ?></option>
                    </select>
                </div>
                
                <h4 class="mt-4"><?php echo $L['vaccinations'] ?? 'Vaccinations'; ?></h4>
                <div id="vaccination-container">
                    <div class="vaccination-entry mb-3 border p-3">
                        <div class="mb-3">
                            <label class="form-label"><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?></label>
                            <select class="form-select" name="vaccine_type[]">
                                <option value=""><?php echo $L['select_vaccine_type'] ?? 'Select Vaccine Type'; ?></option>
                                <option value="rabies"><?php echo $L['rabies'] ?? 'Rabies'; ?></option>
                                <option value="distemper"><?php echo $L['distemper'] ?? 'Distemper'; ?></option>
                                <option value="parvovirus"><?php echo $L['parvovirus'] ?? 'Parvovirus'; ?></option>
                                <option value="other"><?php echo $L['other'] ?? 'Other'; ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?></label>
                            <input type="text" class="form-control" name="vaccine_name[]">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo $L['batch_number'] ?? 'Batch Number'; ?></label>
                                <input type="text" class="form-control" name="batch_number[]">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                                <input type="text" class="form-control" name="veterinarian[]">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?></label>
                                <input type="date" class="form-control" name="vaccination_date[]">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></label>
                                <input type="date" class="form-control" name="expiry_date[]">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="add-vaccination"><?php echo $L['add_vaccination'] ?? 'Add Vaccination'; ?></button>
            </div>
        </div>
        
        <h4 class="mt-4"><?php echo $L['health_checks'] ?? 'Health Checks'; ?></h4>
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label for="clinical_exam" class="form-label"><?php echo $L['information_on_clinical_examinations'] ?? 'Information on Clinical Examinations'; ?></label>
                    <textarea class="form-control" id="clinical_exam" name="clinical_exam"><?php echo isset($_POST['clinical_exam']) ? htmlspecialchars($_POST['clinical_exam']) : ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="animal_statement" class="form-label"><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?></label>
                    <textarea class="form-control" id="animal_statement" name="animal_statement"><?php echo isset($_POST['animal_statement']) ? htmlspecialchars($_POST['animal_statement']) : ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="condition" class="form-label"><?php echo $L['condition'] ?? 'Condition'; ?></label>
                    <select class="form-select" id="condition" name="condition">
                        <option value="good" <?php echo (isset($_POST['condition']) && $_POST['condition'] === 'good') ? 'selected' : ''; ?>><?php echo $L['good'] ?? 'Good'; ?></option>
                        <option value="not_good" <?php echo (isset($_POST['condition']) && $_POST['condition'] === 'not_good') ? 'selected' : ''; ?>><?php echo $L['not_good'] ?? 'Not Good'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="health_notes" class="form-label"><?php echo $L['additional_notes'] ?? 'Additional Notes'; ?></label>
                    <textarea class="form-control" id="health_notes" name="health_notes"><?php echo isset($_POST['health_notes']) ? htmlspecialchars($_POST['health_notes']) : ''; ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><?php echo $L['save'] ?? 'Save'; ?></button>
            <a href="pets.php" class="btn btn-secondary"><?php echo $L['cancel'] ?? 'Cancel'; ?></a>
        </div>
    </form>
</div>

<script>
document.getElementById('add-vaccination').addEventListener('click', function() {
    const container = document.getElementById('vaccination-container');
    const newEntry = container.firstElementChild.cloneNode(true);
    
    // Clear values in cloned entry
    const inputs = newEntry.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.type !== 'button') {
            input.value = '';
        }
    });
    
    container.appendChild(newEntry);
});
</script>

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