<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$current_page = 'cart';
$user_id = $_SESSION['user_id'];

// Fetch cart items
$sql = "SELECT add_to_cart.*, products.name, products.image_url, storage_options.storage 
        FROM add_to_cart
        JOIN products ON add_to_cart.product_id = products.product_id
        JOIN storage_options ON add_to_cart.storage_id = storage_options.storage_id
        WHERE add_to_cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success" role="alert">
        <?php echo $_SESSION['success'];
        unset($_SESSION['success']); ?>
    </div>
<?php elseif (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $_SESSION['error'];
        unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>



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
                <li>
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
            <h1>Your Cart</h1>
            <?php if ($cart_items->num_rows > 0): ?>
                <?php
                $items = [];
                $subtotal = 0;
                while ($row = $cart_items->fetch_assoc()) {
                    $row['line_total'] = floatval($row['price']) * intval($row['quantity']);
                    $subtotal += $row['line_total'];
                    $items[] = $row;
                }
                ?>
                <div class="cart-layout">
                    <div class="cart-list">
                        <?php foreach ($items as $item): ?>
                            <div class="cart-card" data-cart-id="<?php echo intval($item['cart_id']); ?>">
                                <div class="cart-media">
                                    <img src="../upload/<?php echo htmlspecialchars($item['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="cart-info">
                                    <div class="cart-title-row">
                                        <h3 class="cart-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <button class="icon-btn remove-item" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="cart-variants">
                                        <span class="chip">Storage: <?php echo htmlspecialchars($item['storage']); ?></span>
                                        <?php if (!empty($item['color_name'])): ?>
                                            <span class="chip chip-light">Color:
                                                <?php echo htmlspecialchars($item['color_name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-actions-row">
                                        <div class="qty-stepper">
                                            <button class="stepper-btn dec">-</button>
                                            <input type="number" class="qty-input" min="1"
                                                value="<?php echo intval($item['quantity']); ?>">
                                            <button class="stepper-btn inc">+</button>
                                        </div>
                                        <div class="line-total">
                                            Rs <span
                                                class="line-total-amount"><?php echo number_format($item['line_total'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <aside class="cart-summary">
                        <div class="summary-card">
                            <h3>Order Summary</h3>
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <strong>Rs <span
                                        id="cart-subtotal"><?php echo number_format($subtotal, 2); ?></span></strong>
                            </div>
                            <div class="summary-row small">
                                <span>Shipping</span>
                                <span>Calculated at checkout</span>
                            </div>
                            <a href="checkout.php" class="btn btn-success btn-block">Proceed to Checkout</a>
                            <a href="category.php" class="btn btn-secondary btn-block" style="margin-top:10px;">Continue
                                Shopping</a>
                        </div>
                    </aside>
                </div>
            <?php else: ?>
                <div class="cart-empty">
                    <div class="empty-illustration">🛒</div>
                    <h3>Your cart is empty</h3>
                    <p>Explore our latest products and add your favorites.</p>
                    <a href="category.php" class="btn btn-primary">Browse products</a>
                </div>
            <?php endif; ?>
            <div id="cartToast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>
        </div>
    </div>

    <script>
        // Lightweight toast
        function showCartToast(message, type = 'success') {
            const toast = document.getElementById('cartToast');
            if (!toast) return;
            toast.className = 'toast ' + (type === 'error' ? 'error' : 'success');
            toast.textContent = message;
            requestAnimationFrame(() => toast.classList.add('show'));
            clearTimeout(window.__cartToastTimer);
            window.__cartToastTimer = setTimeout(() => toast.classList.remove('show'), 2000);
        }

        async function updateCartQuantity(cardEl, newQty) {
            const cartId = parseInt(cardEl.getAttribute('data-cart-id'), 10);
            const fd = new FormData();
            fd.append('cart_id', String(cartId));
            fd.append('quantity', String(newQty));
            const res = await fetch('update_cart_item.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            if (res.status === 401) { window.location.href = 'login.php'; return; }
            const data = await res.json().catch(() => ({ success: false }));
            if (!data.success) {
                showCartToast(data.message || 'Failed to update quantity', 'error');
                return null;
            }
            return data;
        }

        async function removeCartItem(cardEl) {
            const cartId = parseInt(cardEl.getAttribute('data-cart-id'), 10);
            const fd = new FormData();
            fd.append('cart_id', String(cartId));
            const res = await fetch('remove_cart_item.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            if (res.status === 401) { window.location.href = 'login.php'; return; }
            const data = await res.json().catch(() => ({ success: false }));
            if (!data.success) {
                showCartToast(data.message || 'Failed to remove item', 'error');
                return null;
            }
            return data;
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.cart-card').forEach(card => {
                const qtyInput = card.querySelector('.qty-input');
                const incBtn = card.querySelector('.inc');
                const decBtn = card.querySelector('.dec');
                const removeBtn = card.querySelector('.remove-item');
                const lineTotalEl = card.querySelector('.line-total-amount');

                function parseCurrency(str) {
                    return parseFloat((str || '').replace(/,/g, '')) || 0;
                }

                // Derive unit price from line total / qty (server will compute exact on update)
                const unitPrice = parseCurrency(lineTotalEl.textContent) / Math.max(1, parseInt(qtyInput.value, 10) || 1);

                async function applyQty(newQty) {
                    newQty = Math.max(1, parseInt(newQty, 10) || 1);
                    const data = await updateCartQuantity(card, newQty);
                    if (!data) return;
                    qtyInput.value = String(newQty);
                    lineTotalEl.textContent = (unitPrice * newQty).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    document.getElementById('cart-subtotal').textContent = Number(data.cartSubtotal).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    showCartToast('Quantity updated');
                }

                incBtn.addEventListener('click', () => applyQty((parseInt(qtyInput.value, 10) || 1) + 1));
                decBtn.addEventListener('click', () => applyQty((parseInt(qtyInput.value, 10) || 1) - 1));
                qtyInput.addEventListener('change', () => applyQty(qtyInput.value));

                removeBtn.addEventListener('click', async () => {
                    const data = await removeCartItem(card);
                    if (!data) return;
                    card.remove();
                    document.getElementById('cart-subtotal').textContent = Number(data.cartSubtotal).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    showCartToast('Item removed');
                    if (!document.querySelector('.cart-card')) {
                        // If no items left, reload to show empty state
                        window.location.reload();
                    }
                });
            });
        });
    </script>
</body>

</html>