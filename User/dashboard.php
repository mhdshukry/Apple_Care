<?php
session_start();
$current_page = 'dashboard';
if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}
$user_id = (int) $_SESSION['user_id'];
include '../config.php';

// Save profile changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_id'])) {
    $customer_id = (int) $_POST['customer_id'];
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip_code = $_POST['zip_code'] ?? '';
    $country = $_POST['country'] ?? '';
    $sql = "UPDATE customers SET first_name=?, last_name=?, phone_number=?, address=?, city=?, state=?, zip_code=?, country=? WHERE customer_id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssssssssi', $first_name, $last_name, $phone_number, $address, $city, $state, $zip_code, $country, $customer_id);
        $stmt->execute();
        $message = 'Customer data updated successfully!';
    }
}

// Fetch customer and user
$stmt = $conn->prepare("SELECT c.*, u.username, u.email FROM customers c JOIN users u ON c.user_id = u.user_id WHERE c.user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$customer = $res->fetch_assoc();
if (!$customer) {
    $u = $conn->prepare('SELECT username, email FROM users WHERE user_id = ?');
    $u->bind_param('i', $user_id);
    $u->execute();
    $ur = $u->get_result()->fetch_assoc();
    $customer = [
        'customer_id' => null,
        'first_name' => null,
        'last_name' => null,
        'phone_number' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip_code' => null,
        'country' => null,
        'username' => $ur['username'] ?? 'User',
        'email' => $ur['email'] ?? ''
    ];
}
$customer_id = $customer['customer_id'] ?? null;

// Stats
$total_orders = 0;
$pending_orders = 0;
$total_spent = 0.0;
$cart_items = 0;
if ($customer_id) {
    $q = $conn->prepare('SELECT COUNT(*) cnt FROM orders WHERE customer_id = ?');
    $q->bind_param('i', $customer_id);
    $q->execute();
    $total_orders = (int) ($q->get_result()->fetch_assoc()['cnt'] ?? 0);
    $q2 = $conn->prepare("SELECT COUNT(*) cnt FROM orders WHERE customer_id = ? AND LOWER(status) = 'pending'");
    $q2->bind_param('i', $customer_id);
    $q2->execute();
    $pending_orders = (int) ($q2->get_result()->fetch_assoc()['cnt'] ?? 0);
    $q3 = $conn->prepare('SELECT COALESCE(SUM(total_amount),0) total FROM orders WHERE customer_id = ?');
    $q3->bind_param('i', $customer_id);
    $q3->execute();
    $total_spent = (float) ($q3->get_result()->fetch_assoc()['total'] ?? 0);
}
$q4 = $conn->prepare('SELECT COALESCE(SUM(quantity),0) qty FROM add_to_cart WHERE user_id = ?');
$q4->bind_param('i', $user_id);
$q4->execute();
$cart_items = (int) ($q4->get_result()->fetch_assoc()['qty'] ?? 0);

// Recent orders
$recent_orders = [];
if ($customer_id) {
    $ro = $conn->prepare('SELECT o.order_id, o.order_date, o.status, o.total_amount, p.name AS product_name FROM orders o LEFT JOIN products p ON p.product_id = o.product_id WHERE o.customer_id = ? ORDER BY o.order_date DESC LIMIT 5');
    $ro->bind_param('i', $customer_id);
    $ro->execute();
    $rr = $ro->get_result();
    while ($r = $rr->fetch_assoc()) {
        $recent_orders[] = $r;
    }
}

// Sparkline data (last 6 months)
$labels = [];
$values = [];
if ($customer_id) {
    $mt = $conn->prepare("SELECT DATE_FORMAT(order_date, '%Y-%m') ym, COALESCE(SUM(total_amount),0) total FROM orders WHERE customer_id = ? AND order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym");
    $mt->bind_param('i', $customer_id);
    $mt->execute();
    $mtr = $mt->get_result();
    $map = [];
    while ($row = $mtr->fetch_assoc()) {
        $map[$row['ym']] = (float) $row['total'];
    }
    for ($i = 5; $i >= 0; $i--) {
        $ts = strtotime("-{$i} month");
        $ym = date('Y-m', $ts);
        $labels[] = date('M', $ts);
        $values[] = $map[$ym] ?? 0;
    }
}

// Profile completion
$fields = ['first_name', 'last_name', 'phone_number', 'address', 'city', 'state', 'zip_code', 'country'];
$filled = 0;
foreach ($fields as $f) {
    if (!empty($customer[$f]))
        $filled++;
}
$completion = round(($filled / max(1, count($fields))) * 100);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+</title>
    <link rel="icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="shortcut icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="stylesheet" href="../Assets/CSS/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="../Assets/Images/apple.png" alt="Logo">
                <span>Apple Care+</span>
            </div>
            <ul>
                <li class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">
                    <a href="./home.php">
                        <i class="fa fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'products') ? 'active' : ''; ?>">
                    <a href="./category.php">
                        <i class="fa fa-mobile"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'cart') ? 'active' : ''; ?>">
                    <a href="./cart.php">
                        <i class="fas fa-cart-plus"></i>
                        <span>Add to Cart</span>
                    </a>
                </li>
                <li>
                    <a href="./about.php">
                        <i class="fa fa-user"></i>
                        <span>About Us</span>
                    </a>
                </li>
                <li>
                    <a href="./contact-us.php">
                        <i class="fa fa-info"></i>
                        <span>Contact</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <a href="./dashboard.php">
                        <i class="fa fa-user"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <p>© 2024 by <span>Apple Care</span></p>
                <p>Made with <span style="color: red;">❤</span> by SKR_ATH7</p>
            </div>
            <div class="social-media-footer">
                <ul>
                    <li><a href="#" class="social-media-link"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#" class="social-media-link"><i class="fab fa-facebook"></i></a></li>
                    <li><a href="#" class="social-media-link"><i class="fab fa-linkedin"></i></a></li>
                    <li><a href="#" class="social-media-link"><i class="fab fa-instagram"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="main-content">
            <div class="db-hero">
                <div>
                    <h1>Welcome back, <?php echo htmlspecialchars($customer['username'] ?? 'User'); ?></h1>
                    <p class="muted">Here’s a quick overview of your account activity</p>
                </div>
            </div>
            <?php if (!empty($message)): ?>
                <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <section class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-label">Orders</div>
                    <div class="kpi-value"><?php echo $total_orders; ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Pending</div>
                    <div class="kpi-value"><?php echo $pending_orders; ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Cart Items</div>
                    <div class="kpi-value"><?php echo $cart_items; ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Total Spent</div>
                    <div class="kpi-value">Rs <?php echo number_format($total_spent, 2); ?></div>
                </div>
            </section>
            <section class="quick-actions">
                <a href="category.php" class="qa-btn"><i class="fa fa-mobile"></i> Browse Products</a>
                <a href="cart.php" class="qa-btn"><i class="fas fa-shopping-cart"></i> Go to Cart</a>
                <a href="orders.php" class="qa-btn"><i class="fa fa-receipt"></i> My Orders</a>
                <a href="#profile" class="qa-btn"><i class="fa fa-user"></i> Update Profile</a>
                <a href="../Index.php#contact" class="qa-btn"><i class="fa fa-life-ring"></i> Support</a>
            </section>
            <section class="db-grid">
                <div class="db-col">
                    <div class="db-card">
                        <div class="db-card-header">
                            <h3>Recent Orders</h3>
                        </div>
                        <div class="orders-list">
                            <?php if (!empty($recent_orders)):
                                foreach ($recent_orders as $o): ?>
                                    <div class="order-row">
                                        <div>
                                            <div class="order-title">
                                                <?php echo htmlspecialchars($o['product_name'] ?? ('Order #' . $o['order_id'])); ?>
                                            </div>
                                            <div class="order-sub"><?php echo date('M d, Y', strtotime($o['order_date'])); ?> ·
                                                Rs <?php echo number_format($o['total_amount'], 2); ?></div>
                                        </div>
                                        <span
                                            class="status-badge <?php echo strtolower($o['status']) === 'pending' ? 'warn' : 'ok'; ?>"><?php echo htmlspecialchars($o['status']); ?></span>
                                    </div>
                                <?php endforeach; else: ?>
                                <p class="muted">No recent orders.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="db-card">
                        <div class="db-card-header">
                            <h3>Spending (6 months)</h3>
                        </div>
                        <div class="sparkline-wrap"><canvas id="spendSpark" width="600" height="120"></canvas></div>
                    </div>
                </div>
                <div class="db-col">
                    <div class="db-card" id="profile">
                        <div class="db-card-header">
                            <h3>Profile</h3>
                            <div class="progress">
                                <div class="bar" style="width: <?php echo $completion; ?>%"></div>
                            </div>
                            <div class="progress-note"><?php echo $completion; ?>% complete</div>
                        </div>
                        <form method="POST" action="">
                            <?php if (!empty($customer['customer_id'])): ?>
                                <input type="hidden" name="customer_id"
                                    value="<?php echo (int) $customer['customer_id']; ?>">
                            <?php endif; ?>
                            <div class="form-row">
                                <div class="form-group"><label>First Name</label><input type="text" name="first_name"
                                        value="<?php echo htmlspecialchars($customer['first_name'] ?? ''); ?>"></div>
                                <div class="form-group"><label>Last Name</label><input type="text" name="last_name"
                                        value="<?php echo htmlspecialchars($customer['last_name'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Phone</label><input type="text" name="phone_number"
                                        value="<?php echo htmlspecialchars($customer['phone_number'] ?? ''); ?>"></div>
                                <div class="form-group"><label>City</label><input type="text" name="city"
                                        value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>State</label><input type="text" name="state"
                                        value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>"></div>
                                <div class="form-group"><label>ZIP Code</label><input type="text" name="zip_code"
                                        value="<?php echo htmlspecialchars($customer['zip_code'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-group"><label>Address</label><input type="text" name="address"
                                    value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Country</label><input type="text" name="country"
                                    value="<?php echo htmlspecialchars($customer['country'] ?? ''); ?>"></div>
                            <div class="form-row">
                                <div class="form-group"><label>Email</label><input type="email"
                                        value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" readonly>
                                </div>
                                <div class="form-group"><label>Username</label><input type="text"
                                        value="<?php echo htmlspecialchars($customer['username'] ?? ''); ?>" readonly>
                                </div>
                            </div>
                            <?php if (!empty($customer['customer_id'])): ?><button type="submit"
                                    class="btn btn-primary btn-block">Save Changes</button><?php endif; ?>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script>
        (function () {
            const values = <?php echo json_encode($values); ?>;
            const c = document.getElementById('spendSpark');
            if (!c) return; const ctx = c.getContext('2d'); const W = c.width, H = c.height; ctx.clearRect(0, 0, W, H);
            if (!values || values.length === 0) { ctx.fillStyle = '#999'; ctx.fillText('No data', 10, H / 2); return; }
            const max = Math.max.apply(null, values.concat([1])); const pad = 10; const step = (W - pad * 2) / Math.max(1, values.length - 1);
            ctx.strokeStyle = '#1d4ed8'; ctx.lineWidth = 2; ctx.beginPath();
            values.forEach((v, i) => { const x = pad + i * step; const y = H - pad - (v / max) * (H - pad * 2); if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y); });
            ctx.stroke(); const grad = ctx.createLinearGradient(0, pad, 0, H); grad.addColorStop(0, 'rgba(29,78,216,0.2)'); grad.addColorStop(1, 'rgba(29,78,216,0)');
            ctx.lineTo(W - pad, H - pad); ctx.lineTo(pad, H - pad); ctx.closePath(); ctx.fillStyle = grad; ctx.fill();
        })();
    </script>
</body>

</html>