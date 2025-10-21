<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$current_page = 'cart';

// Fetch cart items for this user
$cart_sql = "SELECT c.cart_id, c.product_id, c.storage_id, c.color_name, c.quantity, c.price,
                    p.name, p.image_url, s.storage
             FROM add_to_cart c
             JOIN products p ON c.product_id = p.product_id
             JOIN storage_options s ON c.storage_id = s.storage_id
             WHERE c.user_id = ?";
$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param('i', $user_id);
$cart_stmt->execute();
$cart_rs = $cart_stmt->get_result();
$cart_items = [];
$subtotal = 0.0;
while ($row = $cart_rs->fetch_assoc()) {
    $row['line_total'] = (float) $row['price'] * (int) $row['quantity'];
    $subtotal += $row['line_total'];
    $cart_items[] = $row;
}

if (empty($cart_items)) {
    $_SESSION['error'] = 'Your cart is empty. Please add items before checkout.';
    header('Location: cart.php');
    exit;
}

// Get or create customer profile for address prefill
$cust_id = 0;
$cust = [
    'first_name' => '',
    'last_name' => '',
    'phone_number' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'zip_code' => '',
    'country' => ''
];
$cq = $conn->prepare('SELECT * FROM customers WHERE user_id = ?');
$cq->bind_param('i', $user_id);
$cq->execute();
$cres = $cq->get_result();
if ($cres && $cres->num_rows) {
    $row = $cres->fetch_assoc();
    $cust_id = (int) $row['customer_id'];
    $cust = array_merge($cust, $row);
} else {
    // Minimal prefill from users table if available
    $uq = $conn->prepare('SELECT username FROM users WHERE user_id = ?');
    $uq->bind_param('i', $user_id);
    $uq->execute();
    $ures = $uq->get_result();
    if ($ures && $ures->num_rows) {
        $uname = $ures->fetch_assoc()['username'] ?? '';
        $cust['first_name'] = $uname; // prefill to something sensible
    }
}

$errors = [];
$success = false;
$applied_coupon = '';
$discount_rate = 0.0; // e.g., 0.10 for 10%

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $country = trim($_POST['country'] ?? 'Sri Lanka');
    $shipping_method = $_POST['shipping_method'] ?? 'standard';
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $applied_coupon = strtoupper(trim($_POST['coupon'] ?? ''));

    if ($first_name === '' || $phone === '' || $address === '' || $city === '' || $state === '' || $zip === '') {
        $errors[] = 'Please fill all required address fields.';
    }

    // Calculate shipping
    $shipping_cost = ($shipping_method === 'express') ? 500.0 : 0.0;

    // Coupon logic: SAVE10 => 10% off subtotal, FREESHIP => shipping free
    if ($applied_coupon === 'SAVE10') {
        $discount_rate = 0.10;
    }
    if ($applied_coupon === 'FREESHIP') {
        $shipping_cost = 0.0;
    }

    $discount_amount = round($subtotal * $discount_rate, 2);
    $grand_total = max(0, ($subtotal - $discount_amount) + $shipping_cost);

    if (empty($errors)) {
        // Upsert customer info
        if ($cust_id > 0) {
            $up = $conn->prepare('UPDATE customers SET first_name=?, last_name=?, phone_number=?, address=?, city=?, state=?, zip_code=?, country=? WHERE customer_id=?');
            $up->bind_param('ssssssssi', $first_name, $last_name, $phone, $address, $city, $state, $zip, $country, $cust_id);
            $up->execute();
        } else {
            $ins = $conn->prepare('INSERT INTO customers (user_id, first_name, last_name, phone_number, address, city, state, zip_code, country) VALUES (?,?,?,?,?,?,?,?,?)');
            $ins->bind_param('issssssss', $user_id, $first_name, $last_name, $phone, $address, $city, $state, $zip, $country);
            $ins->execute();
            $cust_id = $ins->insert_id;
        }

        // Create orders per cart line (consistent with schema); apply discount rate proportionally per line
        $status = 'processing';
        $now = date('Y-m-d H:i:s');
        $ord = $conn->prepare('INSERT INTO orders (customer_id, product_id, order_date, total_amount, status, item_color, storage_id, total_quantity) VALUES (?,?,?,?,?,?,?,?)');
        foreach ($cart_items as $it) {
            $line_total = (float) $it['price'] * (int) $it['quantity'];
            if ($discount_rate > 0) {
                $line_total = round($line_total * (1 - $discount_rate), 2);
            }
            $customer_id_val = (int) $cust_id;
            $product_id_val = (int) $it['product_id'];
            $order_date_val = $now; // string
            $total_amount_val = (float) $line_total; // double
            $status_val = $status; // string
            $item_color_val = $it['color_name']; // string or null -> treat as string
            $storage_id_val = (int) $it['storage_id'];
            $qty_val = (int) $it['quantity'];
            $ord->bind_param('iisdssii', $customer_id_val, $product_id_val, $order_date_val, $total_amount_val, $status_val, $item_color_val, $storage_id_val, $qty_val);
            $ord->execute();
        }

        // Clear cart
        $clr = $conn->prepare('DELETE FROM add_to_cart WHERE user_id = ?');
        $clr->bind_param('i', $user_id);
        $clr->execute();

        $_SESSION['success'] = 'Order placed successfully!';
        header('Location: my_orders.php');
        exit;
    }

    // Keep form values on error
    $cust = array_merge($cust, [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'phone_number' => $phone,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'zip_code' => $zip,
        'country' => $country
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+ - Checkout</title>
    <link rel="icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="shortcut icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="stylesheet" href="../Assets/CSS/home.css">
    <link rel="stylesheet" href="../Assets/CSS/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="container checkout-shell">
        <?php include 'sidebar.php'; ?>
        <div class="main-content checkout-container">
            <div class="checkout-header">
                <div>
                    <h1 class="checkout-title">Checkout</h1>
                    <p class="checkout-subtitle">Secure and fast — review your details and place your order</p>
                </div>
                <div class="progress-track">
                    <div class="progress-node completed"><i class="fas fa-shopping-cart"></i><span>Cart</span></div>
                    <div class="progress-connector completed">
                        <div class="fill"></div>
                    </div>
                    <div class="progress-node active"><i class="fas fa-clipboard-check"></i><span>Details</span></div>
                    <div class="progress-connector active">
                        <div class="fill"></div>
                    </div>
                    <div class="progress-node"><i class="fas fa-box"></i><span>Review</span></div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="margin-bottom:16px;">
                    <?php foreach ($errors as $e) {
                        echo '<div>' . htmlspecialchars($e) . '</div>';
                    } ?>
                </div>
            <?php endif; ?>

            <div class="modern-grid">
                <section class="glass-card elevate">
                    <div class="card-head"><i class="fas fa-map-marker-alt"></i>
                        <h3 class="card-title">Shipping Address</h3>
                    </div>
                    <div class="card-body">
                        <form id="checkout-form" method="post">
                            <div class="f-row">
                                <div class="f-group">
                                    <label class="f-label">First Name</label>
                                    <input class="f-input" type="text" name="first_name"
                                        value="<?php echo htmlspecialchars($cust['first_name']); ?>" required>
                                </div>
                                <div class="f-group">
                                    <label class="f-label">Last Name</label>
                                    <input class="f-input" type="text" name="last_name"
                                        value="<?php echo htmlspecialchars($cust['last_name']); ?>">
                                </div>
                            </div>
                            <div class="f-row">
                                <div class="f-group">
                                    <label class="f-label">Phone</label>
                                    <input class="f-input" type="tel" name="phone"
                                        value="<?php echo htmlspecialchars($cust['phone_number']); ?>" required>
                                </div>
                                <div class="f-group">
                                    <label class="f-label">Country</label>
                                    <input class="f-input" type="text" name="country"
                                        value="<?php echo htmlspecialchars($cust['country'] ?: 'Sri Lanka'); ?>">
                                </div>
                            </div>
                            <div class="f-group">
                                <label class="f-label">Address</label>
                                <input class="f-input" type="text" name="address"
                                    value="<?php echo htmlspecialchars($cust['address']); ?>" required>
                            </div>
                            <div class="f-row">
                                <div class="f-group">
                                    <label class="f-label">City</label>
                                    <input class="f-input" type="text" name="city"
                                        value="<?php echo htmlspecialchars($cust['city']); ?>" required>
                                </div>
                                <div class="f-group">
                                    <label class="f-label">State</label>
                                    <input class="f-input" type="text" name="state"
                                        value="<?php echo htmlspecialchars($cust['state']); ?>" required>
                                </div>
                                <div class="f-group">
                                    <label class="f-label">ZIP</label>
                                    <input class="f-input" type="text" name="zip"
                                        value="<?php echo htmlspecialchars($cust['zip_code']); ?>" required>
                                </div>
                            </div>

                            <div class="card-head" style="padding-top:18px;"><i class="fas fa-shipping-fast"></i>
                                <h3 class="card-title">Delivery</h3>
                            </div>
                            <div class="card-body" style="padding-top:8px;">
                                <div class="opt-row">
                                    <label class="opt-card"><input type="radio" name="shipping_method" value="standard"
                                            checked>
                                        <div><strong>Standard</strong><span>Free • 3-5 business days</span></div>
                                    </label>
                                    <label class="opt-card"><input type="radio" name="shipping_method" value="express">
                                        <div><strong>Express</strong><span>Rs 500 • 1-2 business days</span></div>
                                    </label>
                                </div>
                                <div id="est-delivery" class="muted" style="font-size:12px; margin-top:6px;">Est.
                                    delivery: —</div>
                            </div>

                            <div class="card-head" style="padding-top:4px;"><i class="fas fa-credit-card"></i>
                                <h3 class="card-title">Payment</h3>
                            </div>
                            <div class="card-body" style="padding-top:8px;">
                                <div class="opt-row">
                                    <label class="opt-card"><input type="radio" name="payment_method" value="cod"
                                            checked>
                                        <div><strong>Cash on Delivery</strong><span>Pay when you receive</span></div>
                                    </label>
                                    <label class="opt-card"><input type="radio" name="payment_method" value="card"
                                            disabled>
                                        <div><strong>Card (coming soon)</strong><span>Secure online payment</span></div>
                                    </label>
                                </div>
                            </div>

                            <div class="actions-bar">
                                <div class="coupon-bar">
                                    <input id="coupon-input" class="coupon-input" type="text" name="coupon"
                                        placeholder="Coupon code"
                                        value="<?php echo htmlspecialchars($applied_coupon); ?>">
                                    <button type="button" id="apply-coupon" class="btn">Apply</button>
                                    <span class="pill-note">Try SAVE10 or FREESHIP</span>
                                </div>
                                <div style="display:flex; gap:10px;">
                                    <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                                    <button type="submit" class="btn">Place Order</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>

                <aside>
                    <div class="glass-card summary-card">
                        <div class="summary-head">
                            <h3>Order Summary</h3>
                        </div>
                        <ul class="mini-list">
                            <?php foreach ($cart_items as $it): ?>
                                <?php
                                $raw = $it['image_url'] ?? '';
                                if (preg_match('~^\.\./upload/~', $raw)) {
                                    $imgSrc = $raw; // already correct from User/*
                                } elseif (preg_match('~^upload/~', $raw)) {
                                    $imgSrc = '../' . $raw; // add one level up
                                } else {
                                    $imgSrc = '../upload/' . ltrim($raw, '/');
                                }
                                ?>
                                <li class="mini-item">
                                    <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                        alt="<?php echo htmlspecialchars($it['name']); ?>">
                                    <div class="meta">
                                        <div class="t1"><?php echo htmlspecialchars($it['name']); ?></div>
                                        <div class="t2">Storage:
                                            <?php echo htmlspecialchars($it['storage']); ?>    <?php if (!empty($it['color_name']))
                                                       echo ' • Color: ' . htmlspecialchars($it['color_name']); ?>
                                            • Qty: <?php echo (int) $it['quantity']; ?></div>
                                    </div>
                                    <div class="price">Rs <?php echo number_format($it['line_total'], 2); ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="sum-rows">
                            <div class="sum-row"><span>Subtotal</span><strong>Rs <span
                                        id="co-subtotal"><?php echo number_format($subtotal, 2); ?></span></strong>
                            </div>
                            <div class="sum-row small"><span>Shipping</span><strong id="co-ship">Free</strong></div>
                            <div id="co-discount-row" class="sum-row small"
                                style="<?php echo ($discount_rate > 0 ? '' : 'display:none;'); ?>">
                                <span>Discount (<span
                                        id="co-coupon-label"><?php echo htmlspecialchars($applied_coupon); ?></span>)</span>
                                <strong>- Rs <span
                                        id="co-discount"><?php echo number_format($subtotal * $discount_rate, 2); ?></span></strong>
                            </div>
                            <div class="sum-row total"><span>Total</span><strong>Rs <span
                                        id="co-total"><?php echo number_format(max(0, ($subtotal - ($subtotal * $discount_rate)) + 0), 2); ?></span></strong>
                            </div>
                        </div>
                        <div class="trust-row"><i class="fas fa-lock"></i> Secure checkout • <i class="fas fa-undo"></i>
                            Easy returns</div>
                    </div>
                </aside>
            </div>
            <!-- Mobile sticky total/action bar -->
            <div class="mobile-checkout-bar">
                <div class="total">Total: Rs <span id="co-total-mobile"></span></div>
                <button form="checkout-form" type="submit" class="btn">Place Order</button>
            </div>
        </div>
    </div>

    <script>
        // Update totals when shipping changes
        const shipRadios = document.querySelectorAll('input[name="shipping_method"]');
        const subtotal = parseFloat((document.getElementById('co-subtotal').textContent || '0').replace(/,/g, '')) || 0;
        let discountRate = <?php echo json_encode($discount_rate); ?>;
        let freeShip = <?php echo json_encode(strtoupper($applied_coupon) === 'FREESHIP'); ?>;
        function formatCurrency(n) { return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
        function estimateDelivery(method) {
            const now = new Date();
            const addDays = d => { const dt = new Date(now); dt.setDate(dt.getDate() + d); return dt.toLocaleDateString(); };
            return method === 'express' ? `${addDays(1)} - ${addDays(2)}` : `${addDays(3)} - ${addDays(5)}`;
        }
        function updateTotals() {
            const method = document.querySelector('input[name="shipping_method"]:checked')?.value || 'standard';
            const ship = freeShip ? 0 : (method === 'express' ? 500 : 0);
            document.getElementById('co-ship').textContent = ship ? ('Rs ' + ship.toFixed(2)) : 'Free';
            const total = Math.max(0, (subtotal * (1 - discountRate)) + ship);
            document.getElementById('co-total').textContent = formatCurrency(total);
            const mobile = document.getElementById('co-total-mobile');
            if (mobile) mobile.textContent = formatCurrency(total);
            const est = estimateDelivery(method);
            const estEl = document.getElementById('est-delivery');
            if (estEl) estEl.textContent = 'Est. delivery: ' + est;
        }
        shipRadios.forEach(r => r.addEventListener('change', updateTotals));
        updateTotals();

        // Apply coupon client-side for live preview (server will validate again on submit)
        const applyBtn = document.getElementById('apply-coupon');
        const couponInput = document.getElementById('coupon-input');
        function applyCoupon() {
            const code = (couponInput.value || '').trim().toUpperCase();
            // rules mirror the server
            discountRate = 0;
            if (code === 'SAVE10') discountRate = 0.10;
            freeShip = (code === 'FREESHIP');
            // show/hide discount row
            const discRow = document.getElementById('co-discount-row');
            const discAmt = document.getElementById('co-discount');
            const discLabel = document.getElementById('co-coupon-label');
            if (discountRate > 0) {
                discRow.style.display = '';
                discAmt.textContent = formatCurrency(subtotal * discountRate);
                discLabel.textContent = code;
            } else {
                discRow.style.display = 'none';
            }
            // recompute total
            updateTotals();
        }
        if (applyBtn) applyBtn.addEventListener('click', applyCoupon);

        // Highlight selected option cards
        function syncSelected(group) {
            document.querySelectorAll('label.opt-card').forEach(l => l.classList.remove('selected'));
            document.querySelectorAll(`input[name="${group}"]`).forEach(input => {
                if (input.checked) input.closest('label.opt-card')?.classList.add('selected');
            });
        }
        document.querySelectorAll('input[name="shipping_method"]').forEach(r => {
            r.addEventListener('change', () => { syncSelected('shipping_method'); updateTotals(); });
        });
        document.querySelectorAll('input[name="payment_method"]').forEach(r => {
            r.addEventListener('change', () => syncSelected('payment_method'));
        });
        // initial state
        syncSelected('shipping_method');
        syncSelected('payment_method');
    </script>
</body>

</html>