<?php
session_start();
include '../config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    // If AJAX request, return JSON error; else redirect
    $isAjax = (
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    );
    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'You must be logged in to add to cart.']);
        exit;
    } else {
        $_SESSION['error'] = 'You must be logged in to add to cart.';
        header('Location: ../index.php');
        exit;
    }
}

// Get the required parameters from POST
$product_id = intval($_POST['product_id'] ?? 0);
$user_id = intval($_SESSION['user_id']);
$storage_id = intval($_POST['storage_id'] ?? 0);
$color_name = $_POST['color'] ?? '';
$quantity = intval($_POST['quantity'] ?? 1);

// If product has color options, require color selection
$color_req_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM product_color WHERE product_id = ?");
$color_req_stmt->bind_param('i', $product_id);
$color_req_stmt->execute();
$color_req_res = $color_req_stmt->get_result();
$color_req = $color_req_res ? $color_req_res->fetch_assoc() : ['cnt' => 0];
$requires_color = intval($color_req['cnt'] ?? 0) > 0;
if ($requires_color && ($color_name === null || $color_name === '')) {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    if ($isAjax) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please select a color before adding to cart.']);
        exit;
    } else {
        $_SESSION['error'] = 'Please select a color before adding to cart.';
        header("Location: product_details.php?id=$product_id");
        exit;
    }
}

// Check if the storage option is valid for the selected product
$storage_check_sql = "SELECT price FROM storage_options WHERE storage_id = ? AND product_id = ?";
$storage_check_stmt = $conn->prepare($storage_check_sql);
$storage_check_stmt->bind_param("ii", $storage_id, $product_id);
$storage_check_stmt->execute();
$storage_result = $storage_check_stmt->get_result();

if ($storage_result->num_rows === 0) {
    $message = 'Invalid storage option for this product.';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    if ($isAjax) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
    $_SESSION['error'] = $message;
    header("Location: product_details.php?id=$product_id");
    exit;
}

// Fetch the price for the selected storage option
$storage_data = $storage_result->fetch_assoc();
$price = $storage_data['price'];

// Check if the product is in stock
$stock_check_sql = "SELECT quantity FROM storage_stock WHERE product_id = ? AND storage_id = ?";
$stock_check_stmt = $conn->prepare($stock_check_sql);
$stock_check_stmt->bind_param("ii", $product_id, $storage_id);
$stock_check_stmt->execute();
$stock_result = $stock_check_stmt->get_result();

if ($stock_result->num_rows > 0) {
    $stock_data = $stock_result->fetch_assoc();
    $available_quantity = $stock_data['quantity'];

    if ($quantity > $available_quantity) {
        $message = 'Requested quantity exceeds available stock.';
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        if ($isAjax) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message, 'available' => $available_quantity]);
            exit;
        }
        $_SESSION['error'] = $message;
        header("Location: product_details.php?id=$product_id");
        exit;
    }
} else {
    $message = 'Product is out of stock.';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    if ($isAjax) {
        http_response_code(409);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message, 'available' => 0]);
        exit;
    }
    $_SESSION['error'] = $message;
    header("Location: product_details.php?id=$product_id");
    exit;
}

// Add product to cart
$add_to_cart_sql = "INSERT INTO add_to_cart (user_id, product_id, storage_id, color_name, quantity, price) 
                    VALUES (?, ?, ?, ?, ?, ?)";
// Prepare and execute insertion to cart
$add_to_cart_stmt = $conn->prepare($add_to_cart_sql);
if (!$add_to_cart_stmt) {
    $message = 'Database error: ' . $conn->error;
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
    $_SESSION['error'] = $message;
    header("Location: product_details.php?id=$product_id");
    exit;
}
$add_to_cart_stmt->bind_param("iiisid", $user_id, $product_id, $storage_id, $color_name, $quantity, $price);
if (!$add_to_cart_stmt->execute()) {
    $message = 'Failed to add to cart: ' . $add_to_cart_stmt->error;
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
    $_SESSION['error'] = $message;
    header("Location: product_details.php?id=$product_id");
    exit;
}

// Update stock
$update_stock_sql = "UPDATE storage_stock SET quantity = quantity - ? WHERE product_id = ? AND storage_id = ?";
$update_stock_stmt = $conn->prepare($update_stock_sql);
$update_stock_stmt->bind_param("iii", $quantity, $product_id, $storage_id);
$update_stock_stmt->execute();

// If AJAX, return JSON success; else set session and redirect to cart
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Added to cart',
        'product_id' => $product_id,
        'storage_id' => $storage_id,
        'color' => $color_name,
        'quantity' => $quantity,
        'price' => $price,
        'remaining' => max(0, (int)$available_quantity - (int)$quantity)
    ]);
    exit;
}

$_SESSION['success'] = 'Product added to cart successfully!';
header('Location: cart.php');
exit;

?>
