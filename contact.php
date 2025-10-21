<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$message = trim($_POST['message'] ?? '');

if ($name === '' || !$email || $message === '') {
    $_SESSION['contact_error'] = 'Please fill in all fields with a valid email.';
    header('Location: ' . ($_POST['redirect'] ?? 'Index.php'));
    exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stmt = $conn->prepare('INSERT INTO contact_messages(name,email,message) VALUES (?,?,?)');
$stmt->bind_param('sss', $name, $email, $message);
if ($stmt->execute()) {
    $_SESSION['contact_success'] = 'Message sent! We\'ll get back to you soon.';
} else {
    $_SESSION['contact_error'] = 'Failed to send message. Please try again later.';
}
$stmt->close();

header('Location: ' . ($_POST['redirect'] ?? 'Index.php'));
exit;
