<?php
session_start();
include 'config.php';

function safe_redirect_target($redirect, $fallback = 'Index.php')
{
    $redirect = trim((string) $redirect);
    if ($redirect === '') {
        return $fallback;
    }

    $redirect = str_replace(["\r", "\n"], '', $redirect);
    if (preg_match('#^https?://#i', $redirect) || strpos($redirect, '//') === 0) {
        return $fallback;
    }

    $allowedPrefixes = [
        './',
        '../',
        'Index.php',
        'index.php',
        'products.php',
        'product_details.php',
        'User/',
        'Admin/'
    ];

    foreach ($allowedPrefixes as $prefix) {
        if (stripos($redirect, $prefix) === 0) {
            return $redirect;
        }
    }

    return $fallback;
}

$target = safe_redirect_target($_POST['redirect'] ?? 'Index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Index.php');
    exit;
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$email) {
    $_SESSION['newsletter_error'] = 'Please enter a valid email address.';
    header('Location: ' . $target);
    exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stmt = $conn->prepare('INSERT IGNORE INTO newsletter_subscribers(email) VALUES (?)');
$stmt->bind_param('s', $email);
if ($stmt->execute()) {
    $_SESSION['newsletter_success'] = 'Thanks for subscribing!';
} else {
    $_SESSION['newsletter_error'] = 'Subscription failed. Please try again later.';
}
$stmt->close();

header('Location: ' . $target);
exit;
