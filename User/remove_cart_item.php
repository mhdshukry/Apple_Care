<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$cart_id = intval($_POST['cart_id'] ?? 0);
if ($cart_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Fetch item to restore stock
$item_sql = "SELECT product_id, storage_id, quantity, price FROM add_to_cart WHERE cart_id = ? AND user_id = ?";
$item_stmt = $conn->prepare($item_sql);
$item_stmt->bind_param('ii', $cart_id, $user_id);
$item_stmt->execute();
$res = $item_stmt->get_result();
if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit;
}
$item = $res->fetch_assoc();

$conn->begin_transaction();
try {
    // Restore stock
    $restore = $conn->prepare("UPDATE storage_stock SET quantity = quantity + ? WHERE product_id = ? AND storage_id = ?");
    $restore->bind_param('iii', $item['quantity'], $item['product_id'], $item['storage_id']);
    $restore->execute();

    // Remove from cart
    $del = $conn->prepare("DELETE FROM add_to_cart WHERE cart_id = ? AND user_id = ?");
    $del->bind_param('ii', $cart_id, $user_id);
    $del->execute();

    // Recompute totals
    $tot_sql = "SELECT SUM(quantity*price) as subtotal, SUM(quantity) as total_qty FROM add_to_cart WHERE user_id = ?";
    $tot_stmt = $conn->prepare($tot_sql);
    $tot_stmt->bind_param('i', $user_id);
    $tot_stmt->execute();
    $tot_res = $tot_stmt->get_result();
    $tot = $tot_res->fetch_assoc();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Item removed',
        'cartSubtotal' => round(floatval($tot['subtotal'] ?? 0), 2),
        'totalQty' => intval($tot['total_qty'] ?? 0)
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

?>
