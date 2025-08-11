<?php
require_once 'config.php';
//session_start();
$lang = $_SESSION['lang'] ?? 'en';
$L = require __DIR__ . '/../lang/' . $lang . '.php';

// Jezički izbor (obrada promene jezika)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'sr'])) {
    $_SESSION['lang'] = $_GET['lang'];
    // Redirektuj na istu stranicu bez lang parametra
    $url = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $url");
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Shelter Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Pet Shelter</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pets.php">Pets</a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">Users</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <!-- Language Switcher -->
                    <li class="nav-item me-2">
                        <a href="?lang=en" class="nav-link p-0 <?php echo $lang === 'en' ? 'border border-primary rounded' : ''; ?>" title="English">
                            <img src="https://cdn.jsdelivr.net/gh/hjnilsson/country-flags/svg/gb.svg" alt="English" width="28" height="20">
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a href="?lang=sr" class="nav-link p-0 <?php echo $lang === 'sr' ? 'border border-primary rounded' : ''; ?>" title="Srpski">
                            <img src="https://cdn.jsdelivr.net/gh/hjnilsson/country-flags/svg/rs.svg" alt="Srpski" width="28" height="20">
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="edit-user.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">