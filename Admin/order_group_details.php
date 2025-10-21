<?php
include 'auth.php';
include '../config.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$windowMin = isset($_GET['window']) ? (int) $_GET['window'] : 10; // time window in minutes
if ($windowMin <= 0) {
    $windowMin = 10;
}
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'missing id']);
    exit;
}

// Get reference order and customer info
$sqlRef = "SELECT o.order_id, o.customer_id, o.order_date,
                  c.first_name, c.last_name, c.phone_number, c.address, c.city, c.state, c.zip_code, c.country,
                  u.email
           FROM orders o
           JOIN customers c ON o.customer_id = c.customer_id
           LEFT JOIN users u ON c.user_id = u.user_id
           WHERE o.order_id = ?";
$stmt = $conn->prepare($sqlRef);
$stmt->bind_param('i', $id);
$stmt->execute();
$refRes = $stmt->get_result();
$ref = $refRes->fetch_assoc();
$stmt->close();

if (!$ref) {
    http_response_code(404);
    echo json_encode(['error' => 'not found']);
    exit;
}

// Collect items within time window for the same customer
$sqlItems = "SELECT o.order_id, o.order_date, o.total_amount, o.total_quantity, o.item_color,
                    p.name AS product_name, s.storage
             FROM orders o
             JOIN products p ON o.product_id = p.product_id
             JOIN storage_options s ON o.storage_id = s.storage_id
             WHERE o.customer_id = ?
               AND ABS(TIMESTAMPDIFF(MINUTE, o.order_date, ?)) <= ?
             ORDER BY o.order_date ASC";
$stmt2 = $conn->prepare($sqlItems);
$refDate = $ref['order_date'];
$stmt2->bind_param('isi', $ref['customer_id'], $refDate, $windowMin);
$stmt2->execute();
$itemsRes = $stmt2->get_result();

$items = [];
$grand = 0.0;
$minDate = null;
$maxDate = null;
while ($row = $itemsRes->fetch_assoc()) {
    $qty = (int) $row['total_quantity'];
    $total = (float) $row['total_amount'];
    $unit = $qty > 0 ? ($total / $qty) : $total;
    $row['unit_price'] = $unit;
    $items[] = $row;
    $grand += $total;
    $dt = $row['order_date'];
    if ($minDate === null || $dt < $minDate)
        $minDate = $dt;
    if ($maxDate === null || $dt > $maxDate)
        $maxDate = $dt;
}
$stmt2->close();

echo json_encode([
    'data' => [
        'customer' => [
            'first_name' => $ref['first_name'],
            'last_name' => $ref['last_name'],
            'phone_number' => $ref['phone_number'],
            'address' => $ref['address'],
            'city' => $ref['city'],
            'state' => $ref['state'],
            'zip_code' => $ref['zip_code'],
            'country' => $ref['country'],
            'email' => $ref['email'],
        ],
        'group' => [
            'reference_order_id' => $ref['order_id'],
            'window_minutes' => $windowMin,
            'from_date' => $minDate ?: $ref['order_date'],
            'to_date' => $maxDate ?: $ref['order_date'],
        ],
        'items' => $items,
        'grand_total' => $grand,
        'count' => count($items),
    ]
]);
