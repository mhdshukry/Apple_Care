<?php
include 'auth.php';
include '../config.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'missing id']);
    exit;
}

$sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status, o.total_quantity, o.item_color,
                c.first_name, c.last_name, c.phone_number, c.address, c.city, c.state, c.zip_code, c.country,
                u.email,
                p.product_id, p.name AS product_name, p.image_url,
                s.storage
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        LEFT JOIN users u ON c.user_id = u.user_id
        JOIN products p ON o.product_id = p.product_id
        JOIN storage_options s ON o.storage_id = s.storage_id
        WHERE o.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'not found']);
    exit;
}

echo json_encode(['data' => $row]);
