<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>