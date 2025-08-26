<?php
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['pet_id'])) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: pets.php');
    exit();
}

$pet_id = $_POST['pet_id'];
$vaccine_type = $_POST['vaccine_type'];
$vaccine_name = $_POST['vaccine_name'];
$batch_number = $_POST['batch_number'] ?? null;
$veterinarian = $_POST['veterinarian'] ?? null;
$vaccination_date = $_POST['vaccination_date'];
$expiry_date = $_POST['expiry_date'] ?? null;

// Validacija
if (empty($vaccine_type) || empty($vaccine_name) || empty($vaccination_date)) {
    $_SESSION['error'] = 'Vaccine type, name and date are required';
    header("Location: view-pet.php?id=$pet_id");
    exit();
}

// Provera da li ljubimac postoji i da li korisnik ima prava
$pet = $db->fetchOne("SELECT * FROM pets WHERE id = ?", [$pet_id]);
if (!$pet || ($pet['created_by'] != $_SESSION['user_id'] && !isAdmin())) {
    $_SESSION['error'] = 'Pet not found or you don\'t have permission';
    header('Location: pets.php');
    exit();
}

// Unos vakcinacije
$db->query(
    "INSERT INTO vaccinations (pet_id, vaccine_type, vaccine_name, batch_number, vaccination_date, expiry_date, veterinarian) 
    VALUES (?, ?, ?, ?, ?, ?, ?)",
    [$pet_id, $vaccine_type, $vaccine_name, $batch_number, $vaccination_date, $expiry_date, $veterinarian]
);

$_SESSION['message'] = 'Vaccination record added successfully';
header("Location: view-pet.php?id=$pet_id");
exit();
?>