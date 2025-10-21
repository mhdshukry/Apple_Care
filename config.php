<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apple";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure consistent charset to avoid encoding issues and improve safety
if (function_exists('mysqli_set_charset')) {
    $conn->set_charset('utf8mb4');
}

// Lightweight security headers that won't break inline scripts/styles
// Note: These must be sent before any output
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: no-referrer-when-downgrade');
}
?>