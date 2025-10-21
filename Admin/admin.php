<?php
include 'auth.php';
include '../config.php';

$current_page = 'admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+</title>
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

<body>
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
                <li>
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
                <form class="search" action="orders.php" method="get">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" placeholder="Search orders, products…" />
                </form>
                <div class="topbar-actions">
                    <button id="themeToggle" type="button" class="icon-btn" title="Toggle theme"
                        aria-label="Toggle theme">
                        <i class="far fa-moon"></i>
                    </button>
                    <a class="icon-btn" href="orders.php" title="Notifications" aria-label="Notifications">
                        <i class="far fa-bell"></i>
                        <span class="badge">3</span>
                    </a>
                    <?php $adminInitial = strtoupper(substr(isset($_SESSION['username']) ? $_SESSION['username'] : 'A', 0, 1)); ?>
                    <div class="avatar" title="Profile" aria-label="Profile">
                        <?php echo htmlspecialchars($adminInitial); ?>
                    </div>
                </div>
            </div>
            <header class="adm-header">
                <div>
                    <h1>Dashboard</h1>
                    <p class="muted">Quick insights, health, and actions at a glance</p>
                </div>
                <div class="adm-actions"></div>
            </header>

            <?php
            $adminName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
            // Fulfillment health (last 14 days)
            $qTot = $conn->query("SELECT COUNT(*) c FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)");
            $qDone = $conn->query("SELECT COUNT(*) c FROM orders WHERE status IN ('delivered','completed') AND order_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)");
            $tot = (int) ($qTot ? ($qTot->fetch_assoc()['c'] ?? 0) : 0);
            $done = (int) ($qDone ? ($qDone->fetch_assoc()['c'] ?? 0) : 0);
            $fulfill = $tot > 0 ? round(($done / $tot) * 100) : 0;
            ?>
            <section class="hero">
                <div class="hero-left">
                    <div class="hero-eyebrow">✨ Welcome back</div>
                    <h2 class="hero-title">Hi, <?php echo htmlspecialchars($adminName); ?> <span
                            aria-hidden="true">👋</span></h2>
                    <p class="hero-subtitle">Here’s what’s happening across your store today.</p>
                    <div class="chips">
                        <span class="chip">Fast dispatch 🚚</span>
                        <span class="chip light">Genuine stock 🛡️</span>
                        <span class="chip light">Happy customers ⭐</span>
                    </div>
                </div>
                <div class="hero-right">
                    <div class="gauge <?php echo $fulfill < 50 ? 'bad' : ($fulfill < 80 ? 'warn' : ''); ?>"
                        style="--p: <?php echo $fulfill; ?>"></div>
                    <div class="gauge-label">Fulfillment • <?php echo $fulfill; ?>%</div>
                </div>
            </section>

            <section class="kpi-grid">
                <?php
                // KPIs
                $totalProducts = (int) ($conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'] ?? 0);
                $lowStock = (int) ($conn->query("SELECT COUNT(*) AS c FROM storage_options s LEFT JOIN storage_stock ss ON s.storage_id = ss.storage_id GROUP BY s.storage_id HAVING COALESCE(SUM(ss.quantity),0) < 5")->num_rows ?? 0);
                $openOrders = (int) ($conn->query("SELECT COUNT(*) AS c FROM orders WHERE status NOT IN ('completed','cancelled')")->fetch_assoc()['c'] ?? 0);
                $revenueToday = (float) ($conn->query("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE DATE(order_date)=CURDATE()")->fetch_assoc()['t'] ?? 0);
                ?>
                <article class="kpi t-iris">
                    <div class="kpi-icon">📦</div>
                    <div>
                        <h3>Total Products</h3>
                        <div class="kpi-value"><?php echo $totalProducts; ?></div>
                    </div>
                </article>
                <article class="kpi t-rose">
                    <div class="kpi-icon">⚠️</div>
                    <div>
                        <h3>Low Stock SKUs</h3>
                        <div class="kpi-value"><?php echo $lowStock; ?></div>
                    </div>
                </article>
                <article class="kpi t-mint">
                    <div class="kpi-icon">🧾</div>
                    <div>
                        <h3>Open Orders</h3>
                        <div class="kpi-value"><?php echo $openOrders; ?></div>
                    </div>
                </article>
                <article class="kpi t-sun">
                    <div class="kpi-icon">💰</div>
                    <div>
                        <h3>Revenue Today</h3>
                        <div class="kpi-value">Rs<?php echo number_format($revenueToday, 2); ?></div>
                    </div>
                </article>
            </section>


            <section class="panels row-charts">
                <div class="panel chart two-col">
                    <div class="panel-head">
                        <h3>Sales (Last 7 days)</h3>
                        <div class="panel-actions">
                            <a class="btn btn-small" href="orders.php">Orders</a>
                        </div>
                    </div>
                    <div class="chart-wrap"><canvas id="salesChart"></canvas></div>
                </div>
                <div class="panel chart">
                    <div class="panel-head">
                        <h3>Status Breakdown</h3>
                    </div>
                    <div class="chart-wrap small"><canvas id="statusChart"></canvas></div>
                </div>
                <div class="panel chart">
                    <div class="panel-head">
                        <h3>Orders by Category</h3>
                    </div>
                    <div class="chart-wrap small"><canvas id="catChart"></canvas></div>
                </div>
            </section>

            <section class="panels row-lists">
                <div class="panel two-col">
                    <div class="panel-head">
                        <h3>Recent Orders</h3>
                    </div>
                    <ul class="list media-list">
                        <?php
                        $rs = $conn->query("SELECT o.order_id, o.total_amount, o.status, DATE_FORMAT(o.order_date,'%b %d') d, p.name, p.image_url FROM orders o LEFT JOIN products p ON p.product_id=o.product_id ORDER BY o.order_date DESC LIMIT 8");
                        if ($rs && $rs->num_rows) {
                            while ($o = $rs->fetch_assoc()) {
                                $img = $o['image_url'] ?: '../Assets/Images/apple.png';
                                $title = '#' . (int) $o['order_id'] . ' · ' . ($o['name'] ? htmlspecialchars($o['name']) : 'Order');
                                echo '<li class="media">'
                                    . '<div class="media-left"><img class="media-thumb" src="' . htmlspecialchars($img) . '" alt=""></div>'
                                    . '<div class="media-body"><div class="media-title">' . $title . '</div><div class="media-sub muted">' . htmlspecialchars($o['d']) . '</div></div>'
                                    . '<div class="media-meta"><span class="pill s-' . htmlspecialchars($o['status']) . '">' . htmlspecialchars($o['status']) . '</span> <span class="amount">Rs' . number_format((float) $o['total_amount'], 2) . '</span></div>'
                                    . '</li>';
                            }
                        } else {
                            echo '<li class="muted">No recent orders</li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <h3>Low Stock</h3>
                    </div>
                    <ul class="list media-list">
                        <?php
                        $ls = $conn->query("SELECT p.name, p.image_url, s.storage, COALESCE(SUM(ss.quantity),0) q
                                                                                 FROM storage_options s
                                                                                 JOIN products p ON p.product_id=s.product_id
                                                                                 LEFT JOIN storage_stock ss ON ss.storage_id=s.storage_id
                                                                                 GROUP BY s.storage_id
                                                                                 HAVING q < 5
                                                                                 ORDER BY q ASC LIMIT 8");
                        if ($ls && $ls->num_rows) {
                            while ($r = $ls->fetch_assoc()) {
                                $img = $r['image_url'] ?: '../Assets/Images/apple.png';
                                echo '<li class="media">'
                                    . '<div class="media-left"><img class="media-thumb" src="' . htmlspecialchars($img) . '" alt=""></div>'
                                    . '<div class="media-body"><div class="media-title">' . htmlspecialchars($r['name']) . '</div><div class="media-sub muted">' . htmlspecialchars($r['storage']) . '</div></div>'
                                    . '<div class="media-meta"><span class="pill warn">' . (int) $r['q'] . ' left</span></div>'
                                    . '</li>';
                            }
                        } else {
                            echo '<li class="muted">No low stock items 🎉</li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <h3>Top Products</h3>
                    </div>
                    <ul class="list media-list">
                        <?php
                        $tp = $conn->query("SELECT p.name, p.image_url, COUNT(*) c
                                                                             FROM orders o JOIN products p ON p.product_id=o.product_id
                                                                             GROUP BY p.product_id
                                                                             ORDER BY c DESC
                                                                             LIMIT 8");
                        if ($tp && $tp->num_rows) {
                            while ($t = $tp->fetch_assoc()) {
                                $img = $t['image_url'] ?: '../Assets/Images/apple.png';
                                echo '<li class="media">'
                                    . '<div class="media-left"><img class="media-thumb" src="' . htmlspecialchars($img) . '" alt=""></div>'
                                    . '<div class="media-body"><div class="media-title">' . htmlspecialchars($t['name']) . '</div></div>'
                                    . '<div class="media-meta"><span class="muted">×' . (int) $t['c'] . '</span></div>'
                                    . '</li>';
                            }
                        } else {
                            echo '<li class="muted">Not enough data</li>';
                        }
                        ?>
                    </ul>
                </div>
            </section>

            <section class="panels row-shortcuts">
                <div class="panel shortcuts three-col">
                    <div class="panel-head">
                        <h3>Shortcuts</h3>
                    </div>
                    <div class="qa-grid colorful">
                        <a class="qa q1" href="add_product.php"><i class="fas fa-plus-circle"></i><span>Add
                                Product</span></a>
                        <a class="qa q2" href="products_admin.php"><i class="fas fa-boxes"></i><span>View
                                Inventory</span></a>
                        <a class="qa q3" href="manage_order.php"><i class="fas fa-tasks"></i><span>Manage
                                Orders</span></a>
                        <a class="qa q4" href="orders.php"><i class="fas fa-receipt"></i><span>All Orders</span></a>
                    </div>
                </div>
                <div class="panel status-card">
                    <div class="panel-head">
                        <h3>System Status</h3>
                    </div>
                    <ul class="list">
                        <li>DB Connection <span class="pill ok">OK</span></li>
                        <li>Disk Space <span class="pill ok">Normal</span></li>
                        <li>Backups <span class="pill warn">Due</span></li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
    <script src="../Assets/Js/sidebar.js"></script>
    <script src="../Assets/Js/admin.js?v=20251018"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var ctx = document.getElementById('salesChart');
            if (!ctx) return;
            // Build last 7 days labels
            var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            var now = new Date();
            var labels = [];
            for (var i = 6; i >= 0; i--) { var d = new Date(now); d.setDate(now.getDate() - i); labels.push(days[d.getDay()]); }
            // Fetch approx sales totals from PHP (fallback with zeros if not available)
            <?php
            $sales = [];
            for ($i = 6; $i >= 0; $i--) {
                $q = $conn->query("SELECT COALESCE(SUM(total_amount),0) t FROM orders WHERE DATE(order_date)=CURDATE()-INTERVAL $i DAY");
                $sales[] = (float) ($q ? ($q->fetch_assoc()['t'] ?? 0) : 0);
            }
            echo 'var data = ' . json_encode($sales) . ';';
            ?>
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (Rs)',
                        data: data,
                        fill: true,
                        borderColor: '#111',
                        backgroundColor: 'rgba(17,17,17,0.08)',
                        tension: .35,
                        pointRadius: 3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
            // Status breakdown doughnut
            var sctx = document.getElementById('statusChart');
            if (sctx) {
                <?php
                $st = $conn->query("SELECT status, COUNT(*) c FROM orders GROUP BY status");
                $labels = [];
                $vals = [];
                if ($st) {
                    while ($r = $st->fetch_assoc()) {
                        $labels[] = $r['status'];
                        $vals[] = (int) $r['c'];
                    }
                }
                echo 'var sLabels = ' . json_encode($labels) . '; var sVals = ' . json_encode($vals) . ';';
                ?>
                new Chart(sctx, { type: 'doughnut', data: { labels: sLabels, datasets: [{ data: sVals, backgroundColor: ['#111', '#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#6b7280'] }] }, options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });
            }
            // Orders by Category bar
            var cctx = document.getElementById('catChart');
            if (cctx) {
                <?php
                $cq = $conn->query("SELECT c.name AS cat, COUNT(*) c FROM orders o JOIN products p ON p.product_id=o.product_id JOIN product_categories pc ON pc.product_id=p.product_id JOIN categories c ON c.category_id=pc.category_id GROUP BY c.category_id ORDER BY c DESC LIMIT 6");
                $cl = [];
                $cv = [];
                if ($cq) {
                    while ($x = $cq->fetch_assoc()) {
                        $cl[] = $x['cat'];
                        $cv[] = (int) $x['c'];
                    }
                }
                echo 'var cLabels = ' . json_encode($cl) . '; var cVals = ' . json_encode($cv) . ';';
                ?>
                new Chart(cctx, { type: 'bar', data: { labels: cLabels, datasets: [{ data: cVals, backgroundColor: '#111' }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
            }
        })();
    </script>
</body>

</html>