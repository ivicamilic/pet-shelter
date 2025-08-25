<?php
// filepath: c:\xampp\htdocs\pet-shelter\delete-pet.php
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

// Prvo obriši povezane podatke
$db->query("DELETE FROM vaccinations WHERE pet_id = ?", [$pet_id]);
$db->query("DELETE FROM health_checks WHERE pet_id = ?", [$pet_id]);

// Onda obriši ljubimca
$db->query("DELETE FROM pets WHERE id = ?", [$pet_id]);

// Ako imaš uploadovane slike, možeš i njih obrisati sa servera
if (!empty($pet['image_path']) && file_exists($pet['image_path'])) {
    unlink($pet['image_path']);
}

// Redirekcija na listu ljubimaca
header('Location: pets.php?msg=deleted');
exit();
?>
