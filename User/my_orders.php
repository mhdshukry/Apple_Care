<?php
session_start();
include '../config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
$user_id = (int) $_SESSION['user_id'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Get customer id
$cust = $conn->prepare('SELECT customer_id FROM customers WHERE user_id = ?');
$cust->bind_param('i', $user_id);
$cust->execute();
$cres = $cust->get_result();
$customer_id = ($cres->num_rows ? (int) $cres->fetch_assoc()['customer_id'] : 0);

// Fetch orders
$orders = [];
if ($customer_id) {
    $stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.total_amount, o.status, o.total_quantity, o.item_color,
                                 p.name AS product_name, p.image_url AS product_image, s.storage AS storage
                          FROM orders o
                          LEFT JOIN products p ON p.product_id = o.product_id
                          LEFT JOIN storage_options s ON s.storage_id = o.storage_id
                          WHERE o.customer_id = ? ORDER BY o.order_date DESC");
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $orders[] = $r;
    }
}

// Status map to timeline steps (new workflow)
$steps = ['processing' => 0, 'packed' => 1, 'shipped' => 2, 'delivered' => 3, 'completed' => 4, 'cancelled' => -1];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+ - My Orders</title>
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
        <?php $current_page = 'dashboard';
        include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="db-hero">
                <div>
                    <h1>My Orders</h1>
                    <p class="muted">Track your packages in real-time and manage orders</p>
                </div>
            </div>

            <?php if (empty($orders)): ?>
                <div class="cart-empty">
                    <div class="empty-illustration">📦</div>
                    <h3>No orders yet</h3>
                    <p class="muted">Start shopping to see your orders here.</p>
                    <a class="btn" href="category.php">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($orders as $o):
                        // Normalize status to canonical set: processing, packed, shipped, delivered, completed, cancelled
                        $rawStatus = strtolower(trim($o['status'] ?? ''));
                        switch ($rawStatus) {
                            case 'pending':
                            case 'awaiting':
                            case 'waiting':
                            case 'new':
                            case 'received':
                            case 'in-progress':
                                $status = 'processing';
                                break;
                            case 'packaged':
                                $status = 'packed';
                                break;
                            case 'shipping':
                            case 'dispatched':
                            case 'in transit':
                            case 'in-transit':
                                $status = 'shipped';
                                break;
                            case 'out for delivery':
                            case 'out-for-delivery':
                            case 'arrived':
                                $status = 'delivered';
                                break;
                            case 'complete':
                                $status = 'completed';
                                break;
                            case 'cancel':
                            case 'canceled':
                                $status = 'cancelled';
                                break;
                            case 'processing':
                            case 'packed':
                            case 'shipped':
                            case 'delivered':
                            case 'completed':
                            case 'cancelled':
                                $status = $rawStatus;
                                break;
                            default:
                                $status = 'processing';
                        }
                        $activeIdx = $steps[$status] ?? 0;
                        $isCancelled = ($status === 'cancelled');
                        $isDelivered = ($status === 'delivered');
                        $isCompleted = ($status === 'completed');
                        $withinReturn = false;
                        if ($isDelivered) {
                            $od = strtotime($o['order_date']);
                            $withinReturn = ($od > 0) ? ((time() - $od) <= 7 * 24 * 60 * 60) : false;
                        }
                        ?>
                        <div class="order-card" data-status="<?php echo htmlspecialchars($status); ?>">
                            <div class="order-head">
                                <div class="meta">
                                    <div class="order-id">#<?php echo (int) $o['order_id']; ?></div>
                                    <div class="order-date"><?php echo date('M d, Y', strtotime($o['order_date'])); ?></div>
                                </div>
                                <div class="total">Rs <?php echo number_format((float) $o['total_amount'], 2); ?></div>
                            </div>
                            <div class="order-body">
                                <div class="media">
                                    <?php
                                    $imgSrc = '';
                                    if (!empty($o['product_image'])) {
                                        $raw = $o['product_image'];
                                        // Normalize: remove leading ../ if present
                                        $trimmed = preg_replace('/^\.\.\//', '', $raw);
                                        // Ensure it starts with 'upload/'
                                        if (strpos($trimmed, 'upload/') !== 0) {
                                            $trimmed = 'upload/' . ltrim($trimmed, '/');
                                        }
                                        // From User/ folder, prefix '../' to reach site root
                                        $imgSrc = '../' . $trimmed;
                                    }
                                    ?>
                                    <?php if ($imgSrc): ?>
                                        <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                            alt="<?php echo htmlspecialchars($o['product_name']); ?>">
                                    <?php else: ?>
                                        <div class="img-fallback"><i class="fa fa-mobile"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="details">
                                    <div class="title"><?php echo htmlspecialchars($o['product_name']); ?></div>
                                    <div class="variants">
                                        <span class="chip">Storage: <?php echo htmlspecialchars($o['storage'] ?? '-'); ?></span>
                                        <span class="chip chip-light">Color:
                                            <?php echo htmlspecialchars($o['item_color'] ?? '-'); ?></span>
                                        <span class="chip">Qty: <?php echo (int) $o['total_quantity']; ?></span>
                                        <span
                                            class="status-badge <?php echo $isCancelled ? 'warn' : 'ok'; ?>"><?php echo ucfirst($status); ?></span>
                                    </div>
                                    <?php
                                    // Determine classes for nodes/connectors based on active index
                                    $conn1 = ($activeIdx > 0) ? 'completed' : (($activeIdx === 0) ? 'active' : 'pending');
                                    $conn2 = ($activeIdx > 1) ? 'completed' : (($activeIdx === 1) ? 'active' : 'pending');
                                    $conn3 = ($activeIdx > 2) ? 'completed' : (($activeIdx === 2) ? 'active' : 'pending');
                                    $conn4 = ($activeIdx > 3) ? 'completed' : (($activeIdx === 3) ? 'active' : 'pending');
                                    ?>
                                    <div class="timeline" data-active="<?php echo $activeIdx; ?>">
                                        <div class="node <?php echo ($activeIdx > 0) ? 'active completed' : 'active'; ?>"><i
                                                class="fas fa-cogs"></i><span>Processing</span></div>
                                        <div class="connector <?php echo $conn1; ?>">
                                            <div class="fill"></div>
                                        </div>
                                        <div
                                            class="node <?php echo ($activeIdx > 1) ? 'active completed' : (($activeIdx === 1) ? 'active' : ''); ?>">
                                            <i class="fas fa-box-open"></i><span>Packed</span>
                                        </div>
                                        <div class="connector <?php echo $conn2; ?>">
                                            <div class="fill"></div>
                                        </div>
                                        <div
                                            class="node <?php echo ($activeIdx > 2) ? 'active completed' : (($activeIdx === 2) ? 'active' : ''); ?>">
                                            <i class="fas fa-shipping-fast"></i><span>Shipped</span>
                                        </div>
                                        <div class="connector <?php echo $conn3; ?>">
                                            <div class="fill"></div>
                                        </div>
                                        <div
                                            class="node <?php echo ($activeIdx > 3) ? 'active completed' : (($activeIdx === 3) ? 'active' : ''); ?>">
                                            <i class="fas fa-box"></i><span>Delivered</span>
                                        </div>
                                        <div class="connector <?php echo $conn4; ?>">
                                            <div class="fill"></div>
                                        </div>
                                        <div class="node <?php echo ($activeIdx >= 4) ? 'active completed' : ''; ?>"><i
                                                class="fas fa-check-circle"></i><span>Completed</span></div>
                                    </div>
                                    <div class="actions">
                                        <?php if (!$isCancelled && $activeIdx < 2): ?>
                                            <button class="btn btn-cancel" data-order-id="<?php echo (int) $o['order_id']; ?>"><i
                                                    class="fas fa-times"></i> Cancel Order</button>
                                        <?php endif; ?>
                                        <?php if ($isDelivered && $withinReturn): ?>
                                            <button class="btn btn-return" data-order-id="<?php echo (int) $o['order_id']; ?>"><i
                                                    class="fas fa-undo"></i> Return (7 days)</button>
                                        <?php endif; ?>
                                        <?php if ($isCompleted): ?>
                                            <button class="btn btn-reorder" data-order-id="<?php echo (int) $o['order_id']; ?>"><i
                                                    class="fas fa-redo"></i> Reorder</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="toast" class="toast"></div>
    <script>
        const csrfToken = <?php echo json_encode($csrf_token); ?>;

        // Animate timelines and handle cancel
        document.querySelectorAll('.timeline').forEach(tl => {
            const active = parseInt(tl.getAttribute('data-active') || '0', 10);
            const nodes = tl.querySelectorAll('.node');
            const conns = tl.querySelectorAll('.connector');
            nodes.forEach((n, i) => {
                const delay = i * 100;
                setTimeout(() => {
                    if (i < active) { n.classList.add('active', 'completed'); }
                    else if (i === active) { n.classList.add('active'); }
                }, delay);
            });
            conns.forEach((c, i) => {
                const delay = (i * 100) + 80;
                setTimeout(() => { if (i < active) c.classList.add('completed'); }, delay);
            });
        });

        function showToast(msg, kind = 'success') {
            const t = document.getElementById('toast');
            t.textContent = msg; t.className = 'toast ' + kind + ' show';
            setTimeout(() => { t.classList.remove('show'); }, 2200);
        }

        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-order-id');
                if (!confirm('Cancel this order?')) return;
                try {
                    const body = 'order_id=' + encodeURIComponent(id) + '&csrf_token=' + encodeURIComponent(csrfToken);
                    const res = await fetch('order_cancel.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
                    const data = await res.json();
                    if (data.success) { showToast('Order cancelled'); setTimeout(() => location.reload(), 800); }
                    else { showToast(data.message || 'Cancel failed', 'error'); }
                } catch (e) { showToast('Network error', 'error'); }
            });
        });

        // Return within 7 days (delivered only)
        document.querySelectorAll('.btn-return').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-order-id');
                if (!confirm('Initiate return for this order?')) return;
                try {
                    const body = 'order_id=' + encodeURIComponent(id) + '&csrf_token=' + encodeURIComponent(csrfToken);
                    const res = await fetch('order_return.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
                    const data = await res.json();
                    if (data.success) { showToast('Return requested'); setTimeout(() => location.reload(), 800); }
                    else { showToast(data.message || 'Return failed', 'error'); }
                } catch (e) { showToast('Network error', 'error'); }
            });
        });

        // Reorder (completed orders)
        document.querySelectorAll('.btn-reorder').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-order-id');
                try {
                    const body = 'order_id=' + encodeURIComponent(id) + '&csrf_token=' + encodeURIComponent(csrfToken);
                    const res = await fetch('order_reorder.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
                    const data = await res.json();
                    if (data.success) { showToast('Added to cart'); }
                    else { showToast(data.message || 'Reorder failed', 'error'); }
                } catch (e) { showToast('Network error', 'error'); }
            });
        });
    </script>
</body>

</html>