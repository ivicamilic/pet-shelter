<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();
redirectIfNotAdmin();

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$user_id = $_GET['id'];

// Ne dozvoli brisanje samog sebe
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete yourself!";
    header('Location: users.php');
    exit();
}

// Proveri da li korisnik postoji
$user = getUserById($user_id);
if (!$user) {
    header('Location: users.php');
    exit();
}

// Proveri da li korisnik ima povezane ljubimce
$pets_count = $db->fetchOne("SELECT COUNT(*) as count FROM pets WHERE created_by = ?", [$user_id])['count'];

if ($pets_count > 0) {
    $_SESSION['error'] = "Cannot delete user because they have pets assigned. Reassign pets first.";
    header('Location: users.php');
    exit();
}

// Obriši korisnika
$db->query("DELETE FROM users WHERE id = ?", [$user_id]);

$_SESSION['message'] = "User deleted successfully!";
header('Location: users.php');
exit();
?>