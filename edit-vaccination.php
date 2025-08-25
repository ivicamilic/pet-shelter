<?php

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$lang = $_SESSION['lang'] ?? 'en';
$L = require __DIR__ . '/lang/' . $lang . '.php';
require_once 'includes/functions.php';

$lang = $_SESSION['lang'] ?? 'en';
$L = require __DIR__ . '/lang/' . $lang . '.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pet_id'])) {
    $pet_id = (int)$_POST['pet_id'];

    if (!empty($_POST['vaccination_id'])) {
        foreach ($_POST['vaccination_id'] as $id) {
            $vaccine_type = $_POST['vaccine_type'][$id] ?? '';
            $vaccine_name = trim($_POST['vaccine_name'][$id] ?? '');
            $batch_number = trim($_POST['batch_number'][$id] ?? '');
            $veterinarian = trim($_POST['veterinarian'][$id] ?? '');
            $vaccination_date = $_POST['vaccination_date'][$id] ?? null;
            $expiry_date = $_POST['expiry_date'][$id] ?? null;

            $db->query(
                "UPDATE vaccinations SET 
                    vaccine_type = ?, 
                    vaccine_name = ?, 
                    batch_number = ?, 
                    veterinarian = ?, 
                    vaccination_date = ?, 
                    expiry_date = ?
                 WHERE id = ? AND pet_id = ?",
                [
                    $vaccine_type,
                    $vaccine_name,
                    $batch_number,
                    $veterinarian,
                    $vaccination_date,
                    $expiry_date,
                    $id,
                    $pet_id
                ]
            );
        }
    }

    $_SESSION['message'] = $L['vaccination_updated_successfully'] ??  "Vaccination(s) updated successfully!";
    header("Location: view-pet.php?id=$pet_id");
    exit();
} else {
    header("Location: pets.php");
    exit();
}