<?php
include 'auth.php';
include '../config.php';
$current_page = 'orders';
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+ - Orders</title>
    <link rel="icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="shortcut icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="stylesheet" href="../Assets/CSS/admin.css?v=20251018.3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap"
        rel="stylesheet">
</head>
<style>
    /* Page-scoped polish for colorful, modern UI */
    .status-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 8px 0 14px;
    }

    .chipx {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: var(--panel);
        color: var(--text);
        text-decoration: none;
        font-weight: 700;
    }

    .chipx .badge {
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(0, 0, 0, .06);
        font-weight: 800;
    }

    .chipx.active {
        box-shadow: 0 6px 16px rgba(0, 0, 0, .1);
        border-color: transparent;
    }

    .chipx.proc {
        background: #e8f0ff;
        border-color: #c7dbff;
        color: #1d4ed8;
    }

    .chipx.pack {
        background: #efe9ff;
        border-color: #d8ccff;
        color: #6d28d9;
    }

    .chipx.ship {
        background: #f3e8ff;
        border-color: #e9d5ff;
        color: #7c3aed;
    }

    .chipx.delv {
        background: #eafaf1;
        border-color: #cde9d8;
        color: #15803d;
    }

    .chipx.canc {
        background: #fdecec;
        border-color: #f8c8c8;
        color: #b91c1c;
    }

    .chipx.comp {
        background: #111;
        color: #fff;
        border-color: #000;
    }

    .pill.s-processing {
        background: #e8f0ff;
        color: #1d4ed8;
        border: 1px solid #c7dbff;
    }

    .pill.s-packed {
        background: #efe9ff;
        color: #6d28d9;
        border: 1px solid #d8ccff;
    }

    .pill.s-shipped {
        background: #f3e8ff;
        color: #7c3aed;
        border: 1px solid #e9d5ff;
    }

    .pill.s-delivered {
        background: #eafaf1;
        color: #15803d;
        border: 1px solid #cde9d8;
    }

    .pill.s-completed {
        background: #0f172a;
        color: #fff;
        border: 1px solid #0b1220;
    }

    .pill.s-cancelled {
        background: #fdecec;
        color: #b91c1c;
        border: 1px solid #f8c8c8;
    }

    .table-wrap table thead th {
        position: sticky;
        top: 0;
        background: var(--panel);
        z-index: 1;
    }

    .table-wrap table tbody tr:hover {
        background: rgba(0, 0, 0, 0.03);
    }

    .btn.btn-small {
        padding: 6px 10px;
    }

    /* Nicer View button for Orders */
    .table-wrap .btn.btn-secondary.js-view {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, .35);
        background: linear-gradient(180deg, rgba(255, 255, 255, .85), rgba(255, 255, 255, .7));
        color: #0f172a;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
        transition: all .18s ease;
    }

    .dark .table-wrap .btn.btn-secondary.js-view {
        background: rgba(15, 23, 42, .45);
        color: #e5e7eb;
        border-color: rgba(148, 163, 184, .25);
    }

    .table-wrap .btn.btn-secondary.js-view:hover {
        border-color: #93c5fd;
        box-shadow: 0 6px 16px rgba(2, 132, 199, .15);
        transform: translateY(-1px);
    }

    .table-wrap .btn.btn-secondary.js-view i {
        color: #0284c7;
    }

    .dark .table-wrap .btn.btn-secondary.js-view i {
        color: #7dd3fc;
    }

    .panel .panel-head h3 {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .panel .panel-head h3:before {
        content: "📦";
    }

    /* Modal styling */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal {
        width: min(920px, calc(100% - 56px));
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, .35);
        overflow: hidden;
    }

    .modal-head {
        padding: 16px 18px;
        background: linear-gradient(135deg, #111827 0%, #1f2937 50%, #0f172a 100%);
        color: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .modal-head h3 {
        margin: 0;
        font-weight: 800;
        letter-spacing: -.01em;
    }

    .modal-head .actions {
        display: flex;
        gap: 8px;
    }

    .modal-body {
        padding: 16px 18px;
        display: grid;
        grid-template-columns: 1.1fr 1fr;
        gap: 14px;
        overflow: auto;
        min-height: 0;
    }

    .modal .section {
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 12px;
        background: rgba(255, 255, 255, .6);
    }

    .dark .modal .section {
        background: rgba(15, 23, 42, .5);
    }

    .modal .product-card {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 12px;
        align-items: start;
    }

    .modal .product-card img {
        width: 100%;
        height: auto;
        max-height: 160px;
        object-fit: contain;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: #fff;
    }

    .modal .close-btn {
        background: transparent;
        color: #e5e7eb;
        border: 1px solid rgba(148, 163, 184, .4);
        border-radius: 10px;
        padding: 6px 10px;
        cursor: pointer;
    }

    .modal .print-btn {
        background: #10b981;
        color: #062b1f;
        border: 1px solid rgba(16, 185, 129, .5);
        border-radius: 10px;
        padding: 6px 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .modal .pill {
        font-weight: 700;
    }
</style>

<body class="editor-modern compact">
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="../Assets/Images/apple.png" alt="Logo">
                <span>Apple Care+</span>
            </div>
            <ul>
                <li class="<?php echo ($current_page == 'admin') ? 'active' : ''; ?>">
                    <a href="admin.php">
                        <i class="fa fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="products_admin.php">
                        <i class="fa fa-box"></i>
                        <span>Manage Products</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'manage_order') ? 'active' : ''; ?>">
                    <a href="manage_order.php">
                        <i class="fa fa-tasks"></i>
                        <span>Manage Orders</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'orders') ? 'active' : ''; ?>">
                    <a href="orders.php">
                        <i class="fa fa-list"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li>
                    <a href="../User/logout.php">
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
                    <li>
                        <a href="#" class="social-media-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-media-link">
                            <i class="fab fa-facebook"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-media-link">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-media-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="main-content admin-dashboard">
            <div class="topbar">
                <form class="search" action="#" onsubmit="return false;">
                    <i class="fas fa-search"></i>
                    <input type="text" id="orderFilter" placeholder="Search customer, product, status…" />
                </form>
                <div class="topbar-actions">
                    <button id="themeToggle" type="button" class="icon-btn" title="Toggle theme"
                        aria-label="Toggle theme">
                        <i class="far fa-moon"></i>
                    </button>
                    <?php $adminInitial = strtoupper(substr(isset($_SESSION['username']) ? $_SESSION['username'] : 'A', 0, 1)); ?>
                    <div class="avatar" title="Profile" aria-label="Profile">
                        <?php echo htmlspecialchars($adminInitial); ?></div>
                </div>
            </div>
            <?php
            // Status counts for chips
            $allowedStatuses = ['processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled'];
            $counts = array_fill_keys($allowedStatuses, 0);
            $rsCounts = $conn->query("SELECT status, COUNT(*) c FROM orders GROUP BY status");
            if ($rsCounts) {
                while ($r = $rsCounts->fetch_assoc()) {
                    $st = strtolower($r['status']);
                    if (isset($counts[$st])) {
                        $counts[$st] = (int) $r['c'];
                    }
                }
            }
            $activeCount = $counts['processing'] + $counts['packed'] + $counts['shipped'] + $counts['delivered'] + $counts['cancelled'];
            $filterStatus = strtolower(trim($_GET['status'] ?? ''));
            $isValid = in_array($filterStatus, $allowedStatuses, true);
            $titleNote = $isValid ? ucfirst($filterStatus) . " orders" : "Active orders (exclude Completed)";
            ?>
            <header class="adm-header">
                <div>
                    <h1>Orders</h1>
                    <p class="muted"><?php echo htmlspecialchars($titleNote); ?></p>
                </div>
            </header>
            <div class="panel">
                <div class="panel-head">
                    <h3>Active Orders</h3>
                </div>
                <nav class="status-chips" aria-label="Filter by status">
                    <a class="chipx <?php echo $isValid ? '' : 'active'; ?>" href="orders.php"><span>Active</span><span
                            class="badge"><?php echo $activeCount; ?></span></a>
                    <a class="chipx proc <?php echo $filterStatus === 'processing' ? 'active' : ''; ?>"
                        href="orders.php?status=processing">Processing <span
                            class="badge"><?php echo $counts['processing']; ?></span></a>
                    <a class="chipx pack <?php echo $filterStatus === 'packed' ? 'active' : ''; ?>"
                        href="orders.php?status=packed">Packed <span
                            class="badge"><?php echo $counts['packed']; ?></span></a>
                    <a class="chipx ship <?php echo $filterStatus === 'shipped' ? 'active' : ''; ?>"
                        href="orders.php?status=shipped">Shipped <span
                            class="badge"><?php echo $counts['shipped']; ?></span></a>
                    <a class="chipx delv <?php echo $filterStatus === 'delivered' ? 'active' : ''; ?>"
                        href="orders.php?status=delivered">Delivered <span
                            class="badge"><?php echo $counts['delivered']; ?></span></a>
                    <a class="chipx canc <?php echo $filterStatus === 'cancelled' ? 'active' : ''; ?>"
                        href="orders.php?status=cancelled">Cancelled <span
                            class="badge"><?php echo $counts['cancelled']; ?></span></a>
                    <a class="chipx comp" href="manage_order.php"><span>Completed</span> <span
                            class="badge"><?php echo $counts['completed']; ?></span></a>
                </nav>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Product</th>
                                <th>Color</th>
                                <th>Storage</th>
                                <th>Qty</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersBody">
                            <?php
                            $where = $isValid ? ("o.status='" . $conn->real_escape_string($filterStatus) . "'") : "o.status <> 'completed'";
                            $sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status, o.total_quantity, o.item_color, c.first_name, c.last_name, p.name AS product_name, s.storage
                                    FROM orders o
                                    JOIN customers c ON o.customer_id = c.customer_id
                                    JOIN products p ON o.product_id = p.product_id
                                    JOIN storage_options s ON o.storage_id = s.storage_id
                                    WHERE $where
                                    ORDER BY o.order_date DESC";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . (int) $row['order_id'] . '</td>';
                                    echo '<td>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['order_date']) . '</td>';
                                    echo '<td>Rs' . number_format((float) $row['total_amount'], 2) . '</td>';
                                    echo '<td>';
                                    echo "<form method='post' action='update_order_status.php' style='display:flex; gap:6px; align-items:center'>";
                                    echo "<input type='hidden' name='order_id' value='" . (int) $row['order_id'] . "'>";
                                    echo "<input type='hidden' name='from' value='orders'>";
                                    if ($isValid) {
                                        echo "<input type='hidden' name='status_filter' value='" . htmlspecialchars($filterStatus, ENT_QUOTES) . "'>";
                                    }
                                    echo "<select name='status'>";
                                    $statuses = ['processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled'];
                                    foreach ($statuses as $st) {
                                        $sel = (strtolower((string) $row['status']) === $st) ? 'selected' : '';
                                        echo "<option value='" . $st . "' " . $sel . ">" . ucfirst($st) . "</option>";
                                    }
                                    echo "</select>";
                                    echo "<button type='submit' class='btn btn-small'>Save</button>";
                                    echo "</form>";
                                    echo '</td>';
                                    echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['item_color']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['storage']) . '</td>';
                                    echo '<td>' . (int) $row['total_quantity'] . '</td>';
                                    echo '<td class="table-actions">';
                                    echo '<button class="btn btn-secondary js-view" title="View details" data-id=' . (int) $row['order_id'] . '><i class="fas fa-eye" style="margin-right:6px"></i>View</button>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="10">No orders found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal-overlay" id="orderModal">
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="omTitle">
                <div class="modal-head">
                    <h3 id="omTitle">Order Details</h3>
                    <div class="actions">
                        <button type="button" class="print-btn" id="omPrint"><i class="fas fa-print"></i> Print
                            Quotation</button>
                        <button type="button" class="print-btn" id="omPrintGroup"
                            title="Print quotation for all items ordered around this time"><i
                                class="fas fa-layer-group"></i> Print Group</button>
                        <button type="button" class="close-btn" id="omClose">Close ✕</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="section">
                        <h4>Customer</h4>
                        <div id="omCustomer" class="list"></div>
                    </div>
                    <div class="section">
                        <h4>Summary</h4>
                        <div id="omSummary" class="list"></div>
                    </div>
                    <div class="section" style="grid-column:1/-1">
                        <h4>Product</h4>
                        <div id="omProduct" class="product-card"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../Assets/Js/sidebar.js"></script>
    <script src="../Assets/Js/admin.js?v=20251018"></script>
    <script>
        (function () {
            var input = document.getElementById('orderFilter');
            var rows = Array.from(document.querySelectorAll('#ordersBody tr'));
            function norm(s) { return (s || '').toLowerCase(); }
            function apply() { var q = norm(input.value); rows.forEach(function (tr) { var text = norm(tr.innerText); tr.style.display = text.indexOf(q) !== -1 ? '' : 'none'; }); }
            if (input) { input.addEventListener('input', apply); }
        })();

        // Modal wiring
        (function () {
            var overlay = document.getElementById('orderModal');
            var btnClose = document.getElementById('omClose');
            function open() { overlay.style.display = 'flex'; }
            function close() { overlay.style.display = 'none'; }
            if (btnClose) btnClose.addEventListener('click', close);
            if (overlay) overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
            function pill(status) { var s = (status || '').toLowerCase(); return '<span class="pill s-' + s + '">' + (s.charAt(0).toUpperCase() + s.slice(1)) + '</span>'; }
            function fmtMoney(n) { return 'Rs' + (Number(n || 0)).toFixed(2); }
            function h(s) { return (s == null ? '' : String(s)).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', '\'': '&#39;' }[c]); }); }
            var currentOrder = null;
            function render(d) {
                currentOrder = d;
                var cust = document.getElementById('omCustomer');
                var summ = document.getElementById('omSummary');
                var prod = document.getElementById('omProduct');
                if (!cust || !summ || !prod) return;
                cust.innerHTML = '<div>Name: <strong>' + h(d.first_name + ' ' + d.last_name) + '</strong></div>' +
                    '<div>Email: <strong>' + h(d.email || '') + '</strong></div>' +
                    '<div>Phone: <strong>' + h(d.phone_number || '') + '</strong></div>' +
                    '<div>Address: <strong>' + h(d.address || '') + '</strong></div>' +
                    '<div>Location: <strong>' + h([d.city, d.state, d.zip_code].filter(Boolean).join(', ')) + '</strong></div>' +
                    '<div>Country: <strong>' + h(d.country || '') + '</strong></div>';
                summ.innerHTML = '<div>Status: ' + pill(d.status) + '</div>' +
                    '<div>Date: <strong>' + h(d.order_date) + '</strong></div>' +
                    '<div>Total amount: <strong>' + fmtMoney(d.total_amount) + '</strong></div>' +
                    '<div>Quantity: <strong>' + h(d.total_quantity) + '</strong></div>';
                prod.innerHTML = '<div><img src="' + h(d.image_url) + '" alt=""></div>' +
                    '<div><div><strong>' + h(d.product_name) + '</strong></div><div class="muted">Color: ' + h(d.item_color) + ' · Storage: ' + h(d.storage) + '</div></div>';
            }
            // Company info for letterhead
            var COMPANY = {
                name: 'Apple Care+',
                logo: '/Apple_Care/Assets/Images/apple.png',
                address: 'No. 123, Main Street, Colombo, Sri Lanka',
                phone: '+94 11 123 4567',
                email: 'support@applecare.local',
                socials: [
                    { icon: 'fab fa-facebook', label: '@applecare' },
                    { icon: 'fab fa-instagram', label: '@applecare' },
                    { icon: 'fab fa-twitter', label: '@applecare' },
                    { icon: 'fab fa-linkedin', label: 'Apple Care+' }
                ]
            };
            function buildQuotationHTML(d) {
                var unit = (Number(d.total_amount || 0) / Math.max(1, Number(d.total_quantity || 1)));
                var custLoc = [d.city, d.state, d.zip_code].filter(Boolean).join(', ');
                var od = new Date(d.order_date || Date.now());
                var valid = new Date(od.getTime()); valid.setDate(valid.getDate() + 14);
                function fmtDate(dt) { try { return dt.toISOString().slice(0, 10); } catch (e) { return String(d.order_date || ''); } }
                return `<!DOCTYPE html>
            <html>
            <head>
            <meta charset="utf-8">
                        <title>${h(COMPANY.name)} - Quotation</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <style>
                :root{ --brand:#111827; --accent:#0ea5e9; --accent2:#22c55e; --muted:#374151; --border:#e5e7eb; }
                *{box-sizing:border-box}
                body{font-family:'Nunito', Arial, Helvetica, sans-serif; color:var(--brand); margin:0;}
                .brandbar{height:8px; background:linear-gradient(90deg,var(--accent),var(--accent2));}
                            .container{padding:24px;}
                            .header-split{position:relative; padding-left:96px; text-align:center;}
                            .logo-left{position:absolute; left:0; top:0; width:72px; height:72px; object-fit:contain}
                            .title{margin:0; font-size:22px; letter-spacing:-.01em}
                            .meta{font-size:12px; color:var(--muted)}
                            .socials{margin-top:6px; display:flex; gap:12px; color:var(--brand); font-size:12px; justify-content:center}
                            .socials i{margin-right:6px}
                            .note{color:var(--muted); font-size:12px; margin-top:4px}
                .underline{height:2px; background:linear-gradient(90deg,rgba(0,0,0,.15),transparent); margin:14px 0 18px}
                .sec{margin-bottom:16px}
                .grid{display:grid; grid-template-columns: 1.1fr 1fr; gap:16px}
                .block{border:1px solid var(--border); border-radius:12px; padding:14px}
                .product{display:grid; grid-template-columns: 140px 1fr; gap:12px; align-items:start}
                .product img{width:100%; max-height:140px; object-fit:contain; border:1px solid var(--border); border-radius:10px; background:#fff}
                .tags{display:flex; gap:8px; margin-top:6px}
                .tag{font-size:12px; padding:4px 8px; border-radius:999px; border:1px solid var(--border); background:#f8fafc}
                .tbl{width:100%; border-collapse:collapse; margin-top:10px}
                .tbl thead th{background:#f1f5f9}
                .tbl th,.tbl td{border:1px solid var(--border); padding:8px; text-align:left}
                .tbl tbody tr:nth-child(odd){background:#fafafa}
                .totals{display:flex; justify-content:flex-end}
                .totals .pill{padding:10px 14px; border-radius:12px; background:#ecfeff; border:1px solid #a5f3fc; font-weight:800}
                .terms{font-size:12px; color:var(--muted)}
                .signs{display:grid; grid-template-columns: 1fr 1fr; gap:20px}
                .sign{height:90px}
                .sign .line{margin-top:46px; border-top:1px solid #6b7280; width:70%}
                .footer{position:fixed; bottom:0; left:0; right:0; padding:8px 24px; font-size:12px; color:#374151; display:flex; justify-content:space-between; border-top:1px solid var(--border)}
                .footer .pages:after{content: counter(page) ' / ' counter(pages);} 
                @media print { @page{ margin:14mm; } body{margin:0;} .container{padding:16px 0;} }
            </style>
            </head>
            <body>
                <div class="brandbar"></div>
                    <div class="container">
                        <div class="header-split">
                            <img class="logo-left" src="${h(COMPANY.logo)}" alt="${h(COMPANY.name)}">
                            <div class="centered">
                                <h1 class="title">${h(COMPANY.name)}</h1>
                                <div class="meta">${h(COMPANY.address)} · ${h(COMPANY.phone)} · ${h(COMPANY.email)}</div>
                                <div class="socials">${COMPANY.socials.map(s => `<span><i class="${s.icon}"></i>${h(s.label)}</span>`).join('')}</div>
                                <div class="note">Order #${h(d.order_id)} · Date ${h(d.order_date)} · Valid until ${fmtDate(valid)}</div>
                            </div>
                        </div>
                    <div class="underline"></div>
                    <div class="sec grid">
                        <div class="block">
                            <h3 style="margin:0 0 8px">Product</h3>
                            <div class="product">
                                <img src="${h(d.image_url)}" alt="${h(d.product_name)}">
                                <div>
                                    <div style="font-weight:800; font-size:16px">${h(d.product_name)}</div>
                                    <div class="tags">
                                        <span class="tag"><i class="fas fa-palette"></i> ${h(d.item_color)}</span>
                                        <span class="tag"><i class="fas fa-hdd"></i> ${h(d.storage)}</span>
                                    </div>
                                    <table class="tbl">
                                        <thead><tr><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
                                        <tbody><tr><td>${h(d.total_quantity)}</td><td>Rs${unit.toFixed(2)}</td><td>Rs${Number(d.total_amount || 0).toFixed(2)}</td></tr></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="block">
                            <h3 style="margin:0 0 8px">Customer</h3>
                            <div style="font-weight:800">${h(d.first_name + ' ' + d.last_name)}</div>
                            <div>${h(d.address || '')}</div>
                            <div>${h(custLoc)}</div>
                            <div>${h(d.country || '')}</div>
                            <div style="margin-top:6px">Phone: ${h(d.phone_number || '')} · Email: ${h(d.email || '')}</div>
                        </div>
                    </div>
                    <div class="sec totals"><span class="pill">Grand Total: Rs${Number(d.total_amount || 0).toFixed(2)}</span></div>
                    <div class="sec">
                        <div class="block terms">
                            <strong>Terms & Conditions</strong>
                            <ul>
                                <li>Quotation valid until ${fmtDate(valid)}.</li>
                                <li>Prices include applicable taxes unless otherwise specified.</li>
                                <li>Payment due prior to delivery; bank transfer or card accepted.</li>
                                <li>Warranty as per manufacturer policy.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="sec signs">
                        <div class="sign"><div>Customer Signature</div><div class="line"></div></div>
                        <div class="sign"><div>Authorized Signature</div><div class="line"></div></div>
                    </div>
                </div>
                <div class="footer"><span>${h(COMPANY.name)}</span><span class="pages"></span></div>
            </body>
            </html>`;
            }
            function printQuotation() {
                if (!currentOrder) return; var html = buildQuotationHTML(currentOrder); var w = window.open('', '_blank'); if (!w) { return; } w.document.open(); w.document.write(html); w.document.close(); w.focus(); var tryPrint = function () { try { w.print(); } catch (e) { } }; // small delay to allow resources to load
                setTimeout(tryPrint, 400);
            }
            // Build group quotation (no product images, multiple lines)
            function buildGroupQuotationHTML(payload) {
                var c = payload.customer; var items = payload.items || []; var grand = Number(payload.grand_total || 0);
                var from = payload.group.from_date, to = payload.group.to_date;
                var validText = '';
                try { var vd = new Date(to || Date.now()); vd.setDate(vd.getDate() + 14); validText = vd.toISOString().slice(0, 10); } catch (e) { validText = String(to || ''); }
                function row(i) { return `<tr><td>${i.product_name}</td><td>${i.item_color}</td><td>${i.storage}</td><td>${i.total_quantity}</td><td>Rs${Number(i.unit_price || 0).toFixed(2)}</td><td>Rs${Number(i.total_amount || 0).toFixed(2)}</td></tr>`; }
                return `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${h(COMPANY.name)} - Quotation</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"><style>:root{ --brand:#111827; --accent:#0ea5e9; --accent2:#22c55e; --muted:#374151; --border:#e5e7eb; } *{box-sizing:border-box} body{font-family:'Nunito', Arial, Helvetica, sans-serif; color:var(--brand); margin:0;} .brandbar{height:8px; background:linear-gradient(90deg,var(--accent),var(--accent2));} .container{padding:24px;} .header-split{position:relative; padding-left:96px; text-align:center;} .logo-left{position:absolute; left:0; top:0; width:72px; height:72px; object-fit:contain} .title{margin:0; font-size:22px; letter-spacing:-.01em} .meta{font-size:12px; color:var(--muted)} .socials{margin-top:6px; display:flex; gap:12px; color:var(--brand); font-size:12px; justify-content:center} .socials i{margin-right:6px} .note{color:var(--muted); font-size:12px; margin-top:4px} .underline{height:2px; background:linear-gradient(90deg,rgba(0,0,0,.15),transparent); margin:14px 0 18px} .tbl{width:100%; border-collapse:collapse; margin-top:10px} .tbl thead th{background:#f1f5f9} .tbl th,.tbl td{border:1px solid var(--border); padding:8px; text-align:left} .tbl tbody tr:nth-child(odd){background:#fafafa} .totals{text-align:right; font-weight:800; margin-top:10px} .pill{display:inline-block; padding:10px 14px; border-radius:12px; background:#ecfeff; border:1px solid #a5f3fc; font-weight:800} .terms{font-size:12px; color:var(--muted)} .signs{display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:10px} .sign{height:90px} .sign .line{margin-top:46px; border-top:1px solid #6b7280; width:70%} .footer{position:fixed; bottom:0; left:0; right:0; padding:8px 24px; font-size:12px; color:#374151; display:flex; justify-content:space-between; border-top:1px solid var(--border)} .footer .pages:after{content: counter(page) ' / ' counter(pages);} @media print { @page{ margin:14mm; } body{margin:0;} .container{padding:16px 0;} }</style></head><body><div class='brandbar'></div><div class='container'><div class='header-split'><img class='logo-left' src='${h(COMPANY.logo)}' alt='${h(COMPANY.name)}'><div class='centered'><h1 class='title'>${h(COMPANY.name)}</h1><div class='meta'>${h(COMPANY.address)} · ${h(COMPANY.phone)} · ${h(COMPANY.email)}</div><div class='socials'>${COMPANY.socials.map(s => `<span><i class="${s.icon}"></i>${h(s.label)}</span>`).join('')}</div><div class='note'>Orders between ${h(from)} and ${h(to)} · Valid until ${h(validText)}</div></div></div><div class='underline'></div><div><strong>Customer:</strong> ${h(c.first_name + ' ' + c.last_name)} · ${h(c.address || '')} · ${h([c.city, c.state, c.zip_code].filter(Boolean).join(', '))} · ${h(c.country || '')} · Phone: ${h(c.phone_number || '')} · Email: ${h(c.email || '')}</div><table class='tbl'><thead><tr><th>Product</th><th>Color</th><th>Storage</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead><tbody>${items.map(row).join('')}</tbody></table><div class='totals'><span class='pill'>Grand Total: Rs${grand.toFixed(2)}</span></div><div class='terms'><strong>Terms & Conditions</strong><ul><li>Quotation valid until ${h(validText)}.</li><li>Prices include applicable taxes unless otherwise specified.</li><li>Payment due prior to delivery; bank transfer or card accepted.</li><li>Warranty as per manufacturer policy.</li></ul></div><div class='signs'><div class='sign'><div>Customer Signature</div><div class='line'></div></div><div class='sign'><div>Authorized Signature</div><div class='line'></div></div></div></div><div class='footer'><span>${h(COMPANY.name)}</span><span class='pages'></span></div></body></html>`;
            }
            function printGroupQuotation(orderId, windowMin) { windowMin = windowMin || 10; fetch('order_group_details.php?id=' + encodeURIComponent(orderId) + '&window=' + encodeURIComponent(windowMin)).then(r => r.json()).then(j => { if (!j || !j.data) return; var html = buildGroupQuotationHTML(j.data); var w = window.open('', '_blank'); if (!w) return; w.document.open(); w.document.write(html); w.document.close(); w.focus(); setTimeout(function () { try { w.print(); } catch (e) { } }, 400); }); }
            function fetchAndOpen(id) { fetch('order_details.php?id=' + encodeURIComponent(id)).then(function (r) { return r.json(); }).then(function (j) { if (j && j.data) { render(j.data); open(); } }); }
            document.addEventListener('click', function (e) { var b = e.target.closest('.js-view'); if (!b) return; e.preventDefault(); var id = b.getAttribute('data-id'); if (id) fetchAndOpen(id); });
            var btnPrint = document.getElementById('omPrint'); if (btnPrint) btnPrint.addEventListener('click', printQuotation);
            var btnPrintGroup = document.getElementById('omPrintGroup'); if (btnPrintGroup) btnPrintGroup.addEventListener('click', function () { if (currentOrder) { printGroupQuotation(currentOrder.order_id, 10); } });
        })();
    </script>
</body>

</html>