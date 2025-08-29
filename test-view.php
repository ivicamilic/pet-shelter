<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "Database connection: ";
var_dump($db);

if (!isset($_GET['id'])) {
    echo "No ID provided";
    exit;
}

echo "Pet ID: " . $_GET['id'] . "<br>";

$pet = $db->fetchOne("SELECT * FROM pets WHERE id = ?", [$_GET['id']]);
echo "Pet data: ";
var_dump($pet);
?>