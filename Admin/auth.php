<?php
// Strengthen session cookies for admin area
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Lax'
    ]);
}
session_start();
// Simple admin access check - include this at top of admin pages
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    // Not logged in as admin - redirect to main site
    header('Location: ../index.php');
    exit;
}
?>