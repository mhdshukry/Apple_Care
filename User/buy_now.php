<?php
session_start();
include '../config.php';

// Require login; after login, land on Checkout directly
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=User/checkout.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = max(1, intval($_POST['quantity'] ?? 1));
$storage_id = intval($_POST['storage_id'] ?? 0);
$color_name = $_POST['color'] ?? '';

// Validate inputs
if ($product_id <= 0 || $storage_id <= 0) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: ../product_details.php?id=' . $product_id);
    exit;
}

// If product has color options, require color selection
$colorReq = $conn->prepare('SELECT COUNT(*) AS cnt FROM product_color WHERE product_id = ?');
$colorReq->bind_param('i', $product_id);
$colorReq->execute();
$colorRes = $colorReq->get_result();
$colorRow = $colorRes ? $colorRes->fetch_assoc() : ['cnt' => 0];
if (intval($colorRow['cnt'] ?? 0) > 0 && ($color_name === null || $color_name === '')) {
    $_SESSION['error'] = 'Please select a color before purchasing.';
    header('Location: ../product_details.php?id=' . $product_id);
    exit;
}

// Validate storage belongs to product and get price
$storage_check_sql = 'SELECT price FROM storage_options WHERE storage_id = ? AND product_id = ?';
$storage_check_stmt = $conn->prepare($storage_check_sql);
$storage_check_stmt->bind_param('ii', $storage_id, $product_id);
$storage_check_stmt->execute();
$storage_rs = $storage_check_stmt->get_result();
if ($storage_rs->num_rows === 0) {
    $_SESSION['error'] = 'Invalid storage option for this product.';
    header('Location: ../product_details.php?id=' . $product_id);
    exit;
}
$price = (float) ($storage_rs->fetch_assoc()['price'] ?? 0);

// Check available stock
$stock_stmt = $conn->prepare('SELECT quantity FROM storage_stock WHERE product_id = ? AND storage_id = ?');
$stock_stmt->bind_param('ii', $product_id, $storage_id);
$stock_stmt->execute();
$stock_rs = $stock_stmt->get_result();
if ($stock_rs->num_rows === 0) {
    $_SESSION['error'] = 'Product is out of stock.';
    header('Location: ../product_details.php?id=' . $product_id);
    exit;
}
$avail = (int) $stock_rs->fetch_assoc()['quantity'];
if ($quantity > $avail) {
    $_SESSION['error'] = 'Requested quantity exceeds available stock.';
    header('Location: ../product_details.php?id=' . $product_id);
    exit;
}

// Ensure a customer record exists (needed later in checkout)
$cust = $conn->prepare('SELECT customer_id FROM customers WHERE user_id = ?');
$cust->bind_param('i', $user_id);
$cust->execute();
$cust_rs = $cust->get_result();
if (!$cust_rs || $cust_rs->num_rows === 0) {
    $uq = $conn->prepare('SELECT username FROM users WHERE user_id = ?');
    $uq->bind_param('i', $user_id);
    $uq->execute();
    $ures = $uq->get_result();
    $username = 'Customer';
    if ($ures && $ures->num_rows) {
        $username = $ures->fetch_assoc()['username'] ?? 'Customer';
    }
    $ins = $conn->prepare("INSERT INTO customers (user_id, first_name, last_name) VALUES (?, ?, '')");
    $ins->bind_param('is', $user_id, $username);
    $ins->execute();
}

// Insert into cart
$ins_cart = $conn->prepare('INSERT INTO add_to_cart (user_id, product_id, storage_id, color_name, quantity, price) VALUES (?,?,?,?,?,?)');
$ins_cart->bind_param('iiisid', $user_id, $product_id, $storage_id, $color_name, $quantity, $price);
if (!$ins_cart->execute()) {
    $_SESSION['error'] = 'Failed to add to cart: ' . $ins_cart->error;
    header('Location: ../product_details.php?id=' . $product_id);
    exit;
}

// Reduce stock to match existing add_to_cart behavior
$upd = $conn->prepare('UPDATE storage_stock SET quantity = quantity - ? WHERE product_id = ? AND storage_id = ?');
$upd->bind_param('iii', $quantity, $product_id, $storage_id);
$upd->execute();

// Go straight to checkout
header('Location: checkout.php');
exit;
?>