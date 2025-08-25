<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';


$lang = $_SESSION['lang'] ?? 'en';
$L = require __DIR__ . '/../lang/' . $lang . '.php';

// Jezički izbor (obrada promene jezika)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'sr'])) {
    $_SESSION['lang'] = $_GET['lang'];

    // Kopiramo sve postojeće GET parametre
    $queryParams = $_GET;

    // Uklanjamo 'lang' parametar da se ne bi ponavljao u URL-u
    unset($queryParams['lang']);

    // Uzimamo osnovnu putanju bez ikakvih parametara
    $url = strtok($_SERVER['REQUEST_URI'], '?');

    // Kreiramo novi string sa sačuvanim parametrima
    $newQueryString = http_build_query($queryParams);

    // Sastavljamo finalni URL za preusmeravanje
    $redirectUrl = $url;
    if (!empty($newQueryString)) {
        $redirectUrl .= '?' . $newQueryString;
    }

    header("Location: " . $redirectUrl);
    exit();
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $L['pet_shelter'] ?? 'Pet Shelter'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><?php echo $L['pet_shelter'] ?? 'Pet Shelter'; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><?php echo $L['dashboard'] ?? 'Dashboard'; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pets.php"><?php echo $L['pets'] ?? 'Pets'; ?></a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php"><?php echo $L['users'] ?? 'Users'; ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <!-- Language Switcher -->
<?php
                        // Pravimo ispravne linkove koji čuvaju sve postojeće GET parametre
                        $queryParams = $_GET;

                        // Link za engleski
                        $queryParams['lang'] = 'en';
                        $en_url = '?' . http_build_query($queryParams);

                        // Link za srpski
                        $queryParams['lang'] = 'sr';
                        $sr_url = '?' . http_build_query($queryParams);
                    ?>
                    <li class="nav-item me-2">
                        <a href="<?php echo $en_url; ?>" class="nav-link p-0 <?php echo $lang === 'en' ? 'border border-primary rounded' : ''; ?>" title="English">
                            <img src="https://cdn.jsdelivr.net/gh/hjnilsson/country-flags/svg/gb.svg" alt="English" width="28" height="20">
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a href="<?php echo $sr_url; ?>" class="nav-link p-0 <?php echo $lang === 'sr' ? 'border border-primary rounded' : ''; ?>" title="Srpski">
                            <img src="https://cdn.jsdelivr.net/gh/hjnilsson/country-flags/svg/rs.svg" alt="Srpski" width="28" height="20">
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="edit-user.php"><?php echo $L['profile'] ?? 'Profile'; ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><?php echo $L['logout'] ?? 'Logout'; ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">