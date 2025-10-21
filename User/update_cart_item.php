<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$cart_id = intval($_POST['cart_id'] ?? 0);
$new_qty = intval($_POST['quantity'] ?? 0);

if ($cart_id <= 0 || $new_qty <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Fetch the cart item and related product/storage
$cart_sql = "SELECT c.cart_id, c.user_id, c.product_id, c.storage_id, c.quantity as current_qty, c.price, p.name, p.image_url, s.storage
             FROM add_to_cart c
             JOIN products p ON c.product_id = p.product_id
             JOIN storage_options s ON c.storage_id = s.storage_id
             WHERE c.cart_id = ? AND c.user_id = ?";
$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param('ii', $cart_id, $user_id);
$cart_stmt->execute();
$cart_res = $cart_stmt->get_result();
if ($cart_res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit;
}
$item = $cart_res->fetch_assoc();
$current_qty = intval($item['current_qty']);
$delta = $new_qty - $current_qty;

if ($delta === 0) {
    echo json_encode(['success' => true, 'message' => 'Quantity unchanged', 'itemSubtotal' => round($item['price'] * $new_qty, 2)]);
    exit;
}

// Adjust stock based on delta
$stock_sql = "SELECT quantity FROM storage_stock WHERE product_id = ? AND storage_id = ?";
$stock_stmt = $conn->prepare($stock_sql);
$stock_stmt->bind_param('ii', $item['product_id'], $item['storage_id']);
$stock_stmt->execute();
$stock_res = $stock_stmt->get_result();
if ($stock_res->num_rows === 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Stock information unavailable']);
    exit;
}
$stock_row = $stock_res->fetch_assoc();
$available = intval($stock_row['quantity']);

$conn->begin_transaction();
try {
    if ($delta > 0) {
        // Need more units: check availability
        if ($available < $delta) {
            $conn->rollback();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds available stock.', 'available' => $available + $current_qty]);
            exit;
        }
        // Decrease stock by delta
        $upd_stock = $conn->prepare("UPDATE storage_stock SET quantity = quantity - ? WHERE product_id = ? AND storage_id = ?");
        $upd_stock->bind_param('iii', $delta, $item['product_id'], $item['storage_id']);
        $upd_stock->execute();
    } else {
        // Returning units to stock
        $inc = -$delta;
        $upd_stock = $conn->prepare("UPDATE storage_stock SET quantity = quantity + ? WHERE product_id = ? AND storage_id = ?");
        $upd_stock->bind_param('iii', $inc, $item['product_id'], $item['storage_id']);
        $upd_stock->execute();
    }

    // Update cart quantity
    $upd_cart = $conn->prepare("UPDATE add_to_cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
    $upd_cart->bind_param('iii', $new_qty, $cart_id, $user_id);
    $upd_cart->execute();

    // Compute totals
    $tot_sql = "SELECT SUM(quantity*price) as subtotal, SUM(quantity) as total_qty FROM add_to_cart WHERE user_id = ?";
    $tot_stmt = $conn->prepare($tot_sql);
    $tot_stmt->bind_param('i', $user_id);
    $tot_stmt->execute();
    $tot_res = $tot_stmt->get_result();
    $tot = $tot_res->fetch_assoc();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Quantity updated',
        'itemSubtotal' => round($item['price'] * $new_qty, 2),
        'cartSubtotal' => round(floatval($tot['subtotal'] ?? 0), 2),
        'totalQty' => intval($tot['total_qty'] ?? 0),
        'remaining' => max(0, $available - max(0, $delta))
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

?>
