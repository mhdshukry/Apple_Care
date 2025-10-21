<?php
include 'auth.php';
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

$order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
$status = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : '';
$allowed = ['processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled'];
if (!$order_id || !in_array($status, $allowed, true)) {
    header('Location: orders.php');
    exit;
}

$stmt = $conn->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
$stmt->bind_param('si', $status, $order_id);
$stmt->execute();
$from = isset($_POST['from']) ? $_POST['from'] : '';
$statusFilter = isset($_POST['status_filter']) ? strtolower(trim($_POST['status_filter'])) : '';
$filterValid = in_array($statusFilter, $allowed, true);
$suffix = $filterValid ? ('?status=' . urlencode($statusFilter)) : '';
if ($from === 'manage_order') {
    header('Location: manage_order.php' . $suffix);
} else {
    header('Location: orders.php' . $suffix);
}
exit;