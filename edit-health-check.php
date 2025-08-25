<?php
// filepath: c:\xampp\htdocs\pet-shelter\edit-health-check.php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$lang = $_SESSION['lang'] ?? 'en';
$L = require __DIR__ . '/lang/' . $lang . '.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pet_id'])) {
    $pet_id = (int)$_POST['pet_id'];

    if (!empty($_POST['health_check_id'])) {
        foreach ($_POST['health_check_id'] as $id) {
            $check_date = $_POST['check_date'][$id] ?? null;
            $veterinarian = trim($_POST['veterinarian'][$id] ?? '');
            $health_status = $_POST['health_status'][$id] ?? '';
            $diagnosis = trim($_POST['diagnosis'][$id] ?? '');
            $treatment_plan = trim($_POST['treatment_plan'][$id] ?? '');
            $clinical_exam = trim($_POST['clinical_exam'][$id] ?? '');
            $animal_statement = trim($_POST['animal_statement'][$id] ?? '');
            $health_notes = trim($_POST['health_notes'][$id] ?? '');

            $db->query(
                "UPDATE health_checks SET 
                    check_date = ?, 
                    veterinarian = ?, 
                    health_status = ?, 
                    diagnosis = ?, 
                    treatment_plan = ?, 
                    clinical_exam = ?, 
                    animal_statement = ?, 
                    health_notes = ?
                 WHERE id = ? AND pet_id = ?",
                [
                    $check_date,
                    $veterinarian,
                    $health_status,
                    $diagnosis,
                    $treatment_plan,
                    $clinical_exam,
                    $animal_statement,
                    $health_notes,
                    $id,
                    $pet_id
                ]
            );
        }
    }

    $_SESSION['message'] = "Health check(s) updated successfully!";
    header("Location: view-pet.php?id=$pet_id");
    exit();
} else {
    header("Location: pets.php");
    exit();
}