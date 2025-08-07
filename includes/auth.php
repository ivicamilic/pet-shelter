<?php
require_once 'config.php';
require_once 'db.php';

function loginUser($username, $password) {
    global $db;
    
    $user = $db->fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        return true;
    }
    
    return false;
}

function logoutUser() {
    session_unset();
    session_destroy();
}

function getUserById($id) {
    global $db;
    return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
}
?>