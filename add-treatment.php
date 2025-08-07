<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['pet_id'])) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: pets.php');
    exit();
}

$pet_id = $_POST['pet_id'];
$treatment_type = $_POST['treatment_type'];
$product_name = $_POST['product_name'];
$treatment_date = $_POST['treatment_date'];
$next_treatment_date = $_POST['next_treatment_date'] ?? null;
$veterinarian = $_POST['veterinarian'] ?? null;
$notes = $_POST['notes'] ?? null;

// Validacija
if (empty($treatment_type) || empty($product_name) || empty($treatment_date)) {
    $_SESSION['error'] = 'Treatment type, product name and date are required';
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

// Unos tretmana
$db->query(
    "INSERT INTO treatments (pet_id, treatment_type, product_name, treatment_date, next_treatment_date, veterinarian, notes) 
    VALUES (?, ?, ?, ?, ?, ?, ?)",
    [$pet_id, $treatment_type, $product_name, $treatment_date, $next_treatment_date, $veterinarian, $notes]
);

$_SESSION['message'] = 'Treatment record added successfully';
header("Location: view-pet.php?id=$pet_id");
exit();
?>