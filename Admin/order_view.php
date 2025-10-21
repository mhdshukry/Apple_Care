<?php
include 'auth.php';
include '../config.php';

$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$order_id) {
    echo 'Invalid order id.';
    exit;
}

$sql = "SELECT o.*, 
    c.first_name, c.last_name, c.phone_number, c.address, c.city, c.state, c.zip_code, c.country,
    u.email,
    p.name AS product_name, p.image_url,
    s.storage
  FROM orders o
  JOIN customers c ON o.customer_id = c.customer_id
  LEFT JOIN users u ON c.user_id = u.user_id
  JOIN products p ON o.product_id = p.product_id
  JOIN storage_options s ON o.storage_id = s.storage_id
  WHERE o.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();
if (!$order) {
    echo 'Order not found.';
    exit;
}

$current_page = (strtolower($order['status']) === 'completed') ? 'manage_order' : 'orders';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo (int) $order_id; ?> - Apple Care+</title>
    <link rel="icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="shortcut icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="stylesheet" href="../Assets/CSS/admin.css?v=20251018.3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="editor-modern compact">
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="../Assets/Images/apple.png" alt="Logo">
                <span>Apple Care+</span>
            </div>
            <ul>
                <li><a href="admin.php"><i class="fa fa-home"></i><span>Dashboard</span></a></li>
                <li><a href="products_admin.php"><i class="fa fa-box"></i><span>Manage Products</span></a></li>
                <li class="<?php echo ($current_page == 'manage_order') ? 'active' : ''; ?>"><a href="manage_order.php"><i
                            class="fa fa-tasks"></i><span>Manage Orders</span></a></li>
                <li class="<?php echo ($current_page == 'orders') ? 'active' : ''; ?>"><a href="orders.php"><i
                            class="fa fa-list"></i><span>Orders</span></a></li>
                <li><a href="../User/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </div>
        <div class="main-content admin-dashboard">
            <header class="adm-header">
                <div>
                    <h1>Order #<?php echo (int) $order_id; ?></h1>
                    <p class="muted">Full details for the selected order</p>
                </div>
                <div class="adm-actions">
                    <a href="<?php echo ($current_page === 'manage_order') ? 'manage_order.php' : 'orders.php'; ?>"
                        class="btn btn-small">Back</a>
                </div>
            </header>
            <section class="panels ap-form equal">
                <div class="panel">
                    <h3>Customer</h3>
                    <div class="list">
                        <div>Name:
                            <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong>
                        </div>
                        <div>Email: <strong><?php echo htmlspecialchars($order['email'] ?? ''); ?></strong></div>
                        <div>Phone: <strong><?php echo htmlspecialchars($order['phone_number'] ?? ''); ?></strong></div>
                        <div>Address: <strong><?php echo htmlspecialchars($order['address'] ?? ''); ?></strong></div>
                        <div>Location:
                            <strong><?php echo htmlspecialchars(trim(($order['city'] ?? '') . ', ' . ($order['state'] ?? '') . ' ' . ($order['zip_code'] ?? ''))); ?></strong>
                        </div>
                        <div>Country: <strong><?php echo htmlspecialchars($order['country'] ?? ''); ?></strong></div>
                    </div>
                </div>
                <div class="panel">
                    <h3>Order Summary</h3>
                    <ul class="list">
                        <li>Status: <span
                                class="pill s-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span>
                        </li>
                        <li>Date: <strong><?php echo htmlspecialchars($order['order_date']); ?></strong></li>
                        <li>Total amount:
                            <strong>Rs<?php echo number_format((float) $order['total_amount'], 2); ?></strong></li>
                        <li>Quantity: <strong><?php echo (int) $order['total_quantity']; ?></strong></li>
                    </ul>
                </div>
            </section>
            <section class="panels ap-form one">
                <div class="panel">
                    <h3>Product</h3>
                    <div class="product-detail-grid"
                        style="grid-template-columns: 200px 1fr; gap:12px; align-items:start">
                        <div><img src="<?php echo htmlspecialchars($order['image_url']); ?>" alt=""
                                style="width:100%;border-radius:10px;border:1px solid var(--border);background:#fff">
                        </div>
                        <div>
                            <div><strong><?php echo htmlspecialchars($order['product_name']); ?></strong></div>
                            <div class="muted">Color: <?php echo htmlspecialchars($order['item_color']); ?> · Storage:
                                <?php echo htmlspecialchars($order['storage']); ?></div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="panels ap-form one">
                <div class="panel">
                    <h3>Update Status</h3>
                    <form method="post" action="update_order_status.php"
                        style="display:flex; gap:10px; align-items:center">
                        <input type="hidden" name="order_id" value="<?php echo (int) $order_id; ?>">
                        <input type="hidden" name="from" value="orders">
                        <select name="status">
                            <?php $statuses = ['processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled'];
                            foreach ($statuses as $st) {
                                $sel = ($order['status'] === $st) ? 'selected' : '';
                                echo '<option value="' . $st . '" ' . $sel . '>' . ucfirst($st) . '</option>';
                            } ?>
                        </select>
                        <button type="submit" class="btn btn-gradient">Save</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
    <script src="../Assets/Js/sidebar.js"></script>
    <script src="../Assets/Js/admin.js?v=20251018"></script>
</body>

</html>