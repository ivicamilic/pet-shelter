<?php
session_start();

// Osnovne konfiguracije
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pet-shelter');
define('BASE_URL', 'http://localhost/pet-shelter');

// Autentikacija
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
function isStaff() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}
?>