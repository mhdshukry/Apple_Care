<?php
session_start();
header('Content-Type: application/json');
include '../config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Not authenticated']);
  exit;
}
$user_id = (int) $_SESSION['user_id'];

$postedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (!$postedToken || !$sessionToken || !hash_equals($sessionToken, $postedToken)) {
  echo json_encode(['success' => false, 'message' => 'Security validation failed']);
  exit;
}

$order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
if (!$order_id) {
  echo json_encode(['success' => false, 'message' => 'Invalid order']);
  exit;
}

// Ensure order belongs to this user
$q = $conn->prepare('SELECT o.order_id, o.status FROM orders o JOIN customers c ON o.customer_id=c.customer_id WHERE c.user_id = ? AND o.order_id = ?');
$q->bind_param('ii', $user_id, $order_id);
$q->execute();
$res = $q->get_result();
if ($res->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'Order not found']);
  exit;
}
$row = $res->fetch_assoc();
$status = strtolower($row['status']);

// Business rule: allow cancel only for processing/packed; block from shipped onwards and if already cancelled/completed
if (in_array($status, ['shipped', 'delivered', 'completed', 'cancelled'])) {
  echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled at this stage']);
  exit;
}

$up = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
$up->bind_param('i', $order_id);
$ok = $up->execute();
echo json_encode(['success' => $ok]);