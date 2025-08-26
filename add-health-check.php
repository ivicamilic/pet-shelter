<?php
// filepath: c:\xampp\htdocs\pet-shelter\add-health-check.php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();

if ($_SESSION['role'] === 'Volunteer') {
    $_SESSION['error'] = 'Access denied';
    header('Location: pets.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pet_id'])) {
    $pet_id = (int)$_POST['pet_id'];
    $check_date = $_POST['check_date'] ?? null;
    $veterinarian = trim($_POST['veterinarian'] ?? '');
    $health_status = $_POST['health_status'] ?? '';
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $treatment_plan = trim($_POST['treatment_plan'] ?? '');
    $clinical_exam = trim($_POST['clinical_exam'] ?? '');
    $animal_statement = trim($_POST['animal_statement'] ?? '');
    $health_notes = trim($_POST['health_notes'] ?? '');

    $db->query(
        "INSERT INTO health_checks (pet_id, check_date, veterinarian, health_status, diagnosis, treatment_plan, clinical_exam, animal_statement, health_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$pet_id, $check_date, $veterinarian, $health_status, $diagnosis, $treatment_plan, $clinical_exam, $animal_statement, $health_notes]
    );

    $_SESSION['message'] = "Health check added successfully!";
    header("Location: view-pet.php?id=$pet_id");
    exit();
}

// GET metoda - prikaz forme
if (isset($_GET['pet_id'])) {
    $pet_id = (int)$_GET['pet_id'];
    $pet = getPetById($pet_id);
    if (!$pet) {
        header('Location: pets.php');
        exit();
    }

    // Zabrani volonterima unos
    if ($_SESSION['role'] === 'Volunteer') {
        header('Location: view-pet.php?id=' . $pet_id);
        exit();
    }

    include 'includes/header.php';
    ?>

    <div class="container mt-4">
        <h2>Add Health Check for <?php echo htmlspecialchars($pet['name']); ?></h2>
        <form method="POST">
            <input type="hidden" name="pet_id" value="<?php echo $pet_id; ?>">
            <div class="mb-3">
                <label for="check_date" class="form-label">Check Date</label>
                <input type="date" class="form-control" id="check_date" name="check_date" required>
            </div>
            <div class="mb-3">
                <label for="veterinarian" class="form-label">Veterinarian</label>
                <input type="text" class="form-control" id="veterinarian" name="veterinarian">
            </div>
            <div class="mb-3">
                <label for="health_status" class="form-label">Health Status</label>
                <select class="form-select" id="health_status" name="health_status" required>
                    <option value="">Select status</option>
                    <option value="excellent">Excellent</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="diagnosis" class="form-label">Diagnosis</label>
                <textarea class="form-control" id="diagnosis" name="diagnosis"></textarea>
            </div>
            <div class="mb-3">
                <label for="treatment_plan" class="form-label">Treatment Plan</label>
                <textarea class="form-control" id="treatment_plan" name="treatment_plan"></textarea>
            </div>
            <div class="mb-3">
                <label for="clinical_exam" class="form-label">Clinical Exam</label>
                <textarea class="form-control" id="clinical_exam" name="clinical_exam"></textarea>
            </div>
            <div class="mb-3">
                <label for="animal_statement" class="form-label">Animal Statement</label>
                <textarea class="form-control" id="animal_statement" name="animal_statement"></textarea>
            </div>
            <div class="mb-3">
                <label for="health_notes" class="form-label">Additional Notes</label>
                <textarea class="form-control" id="health_notes" name="health_notes"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save Health Check</button>
            <a href="view-pet.php?id=<?php echo $pet_id; ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <?php
    include 'includes/footer.php';
    exit();
}

// Ako nema ni POST ni GET pet_id, redirektuj na listu ljubimaca
header('Location: pets.php');
exit();