<?php
session_start();
header('Content-Type: application/json');
include '../config.php';
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit; }
$user_id = (int)$_SESSION['user_id'];

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
if (!$order_id) { echo json_encode(['success'=>false,'message'=>'Invalid order']); exit; }

// Get order info (product, storage, color, qty, price)
$q = $conn->prepare('SELECT o.product_id, o.storage_id, o.item_color, o.total_quantity, s.price FROM orders o JOIN customers c ON o.customer_id=c.customer_id JOIN storage_options s ON o.storage_id=s.storage_id WHERE c.user_id=? AND o.order_id=?');
$q->bind_param('ii', $user_id, $order_id);
$q->execute(); $res = $q->get_result();
if ($res->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Order not found']); exit; }
$info = $res->fetch_assoc();

$product_id = (int)$info['product_id'];
$storage_id = (int)$info['storage_id'];
$color = $info['item_color'] ?? '';
$qty = max(1, (int)$info['total_quantity']);
$price = (float)$info['price'];

// Check stock
$stock = $conn->prepare('SELECT quantity FROM storage_stock WHERE product_id=? AND storage_id=?');
$stock->bind_param('ii', $product_id, $storage_id);
$stock->execute(); $rs = $stock->get_result();
if ($rs->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Out of stock']); exit; }
$available = (int)$rs->fetch_assoc()['quantity'];
if ($available <= 0) { echo json_encode(['success'=>false,'message'=>'Out of stock']); exit; }

$qty = min($qty, $available);

// Insert into cart
$ins = $conn->prepare('INSERT INTO add_to_cart (user_id, product_id, storage_id, color_name, quantity, price) VALUES (?,?,?,?,?,?)');
$ins->bind_param('iiisid', $user_id, $product_id, $storage_id, $color, $qty, $price);
$ok = $ins->execute();

if ($ok) {
  // Do not adjust stock yet (cart stage), consistent with add_to_cart.php? There it adjusted stock immediately.
  // For consistency with your current logic, also decrement stock here.
  $upd = $conn->prepare('UPDATE storage_stock SET quantity = quantity - ? WHERE product_id = ? AND storage_id = ?');
  $upd->bind_param('iii', $qty, $product_id, $storage_id);
  $upd->execute();
}

echo json_encode(['success'=>$ok, 'added_qty'=>$qty]);
