<?php
require_once 'db.php';

// Ensure global $db is available
global $db;

function getAllPets($limit = 10) {
    global $db;
    return $db->fetchAll("SELECT * FROM pets ORDER BY id DESC LIMIT ?", [$limit]);
}

function getPetById($id) {
    global $db;
    $pet = $db->fetchOne("SELECT p.*, u.username as created_by_name 
                          FROM pets p 
                          JOIN users u ON p.created_by = u.id 
                          WHERE p.id = ?", [$id]);
    
    if ($pet) {
        $pet['vaccinations'] = $db->fetchAll("SELECT * FROM vaccinations WHERE pet_id = ?", [$id]);
        $pet['treatments'] = $db->fetchAll("SELECT * FROM treatments WHERE pet_id = ?", [$id]);
        $pet['health_checks'] = $db->fetchAll("SELECT * FROM health_checks WHERE pet_id = ? ORDER BY check_date DESC", [$id]);
    }
    
    return $pet;
}

function getRecentActivity($limit = 5) {
    global $db;
    return $db->fetchAll("
        SELECT 'pet' as type, p.id, p.name as title, p.breed, p.microchip_number, p.created_at, u.username as created_by
        FROM pets p
        JOIN users u ON p.created_by = u.id
        UNION
        SELECT 'vaccination' as type, v.id, CONCAT('Vaccination for pet #', v.pet_id) as title, p.breed, p.microchip_number, v.created_at, u.username as created_by
        FROM vaccinations v
        JOIN pets p ON v.pet_id = p.id
        JOIN users u ON p.created_by = u.id
        ORDER BY created_at DESC
        LIMIT ?
    ", [$limit]);
}

function getPetStats() {
    global $db;
    return $db->fetchAll("SELECT status, COUNT(*) as count FROM pets GROUP BY status");
}
?>