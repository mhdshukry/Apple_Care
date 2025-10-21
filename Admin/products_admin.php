<?php
include 'auth.php';
include '../config.php';

$current_page = 'products';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+ - Products</title>
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
                <li>
                    <a href="admin.php">
                        <i class="fa fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="active">
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
                <li>
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
                <p>© 2025 by <span>Apple Care</span></p>
                <p>Made with <span style="color:red;">❤</span> by SKR_ATH7</p>
            </div>
        </div>

        <div class="main-content admin-dashboard">
            <div class="topbar">
                <form class="search" action="products_admin.php" method="get">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" placeholder="Search products…" />
                </form>
                <div class="topbar-actions">
                    <button id="themeToggle" type="button" class="icon-btn" title="Toggle theme"
                        aria-label="Toggle theme">
                        <i class="far fa-moon"></i>
                    </button>
                    <?php $adminInitial = strtoupper(substr(isset($_SESSION['username']) ? $_SESSION['username'] : 'A', 0, 1)); ?>
                    <div class="avatar" title="Profile" aria-label="Profile">
                        <?php echo htmlspecialchars($adminInitial); ?>
                    </div>
                </div>
            </div>

            <header class="adm-header">
                <div>
                    <h1>Manage Products</h1>
                </div>
                <div class="adm-actions">
                    <a href="add_product.php" class="btn btn-gradient"><i class="fas fa-plus-circle"></i> Add
                        Product</a>
                </div>
            </header>

            <div class="panel">
                <div class="panel-head">
                    <h3>Catalog</h3>
                    <div class="panel-actions">
                        <div class="search" style="min-width:260px">
                            <i class="fas fa-search"></i>
                            <input id="tableFilter" type="text" placeholder="Filter by name, model, storage…" />
                        </div>
                        <label class="muted" style="display:flex;align-items:center;gap:6px"><input type="checkbox"
                                id="lowStockOnly"> Low stock only</label>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Model</th>
                                <th>Stock</th>
                                <th>Storage Option</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.product_id, p.name, p.model, p.image_url,
         s.storage, s.price,
         COALESCE(SUM(ss.quantity), 0) AS stock
     FROM products p
     LEFT JOIN storage_options s ON p.product_id = s.product_id
     LEFT JOIN storage_stock ss ON s.storage_id = ss.storage_id
     GROUP BY p.product_id, s.storage, s.price";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr data-stock='" . (int) $row['stock'] . "' onclick=\"if(!event.target.closest('.table-actions')){ window.location.href='product_details.php?id=" . (int) $row['product_id'] . "' }\">";
                                    $img = htmlspecialchars($row['image_url']);
                                    $name = htmlspecialchars($row['name']);
                                    $model = htmlspecialchars($row['model']);
                                    $storage = htmlspecialchars($row['storage']);
                                    $price = number_format((float) $row['price'], 2);
                                    echo "<td><img src='${img}' alt='${name}' width='50' height='50' style='object-fit:contain;border-radius:8px;border:1px solid #eee;background:#fafafa'></td>";
                                    echo "<td>${name}</td>";
                                    echo "<td>${model}</td>";
                                    echo "<td>" . (int) $row['stock'] . "</td>";
                                    echo "<td>${storage}</td>";
                                    echo "<td>Rs${price}</td>";
                                    echo "<td class='table-actions'>";
                                    echo "<a href='edit_product.php?id=" . (int) $row['product_id'] . "' class='btn btn-secondary'><i class='fas fa-edit'></i> Edit</a> ";
                                    echo "<a href='delete_product.php?id=" . (int) $row['product_id'] . "' class='btn btn-danger js-del' data-name='" . $name . "'><i class='fas fa-trash'></i> Delete</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No products found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modern Confirm Dialog Styles and Markup -->
    <style>
        .confirm-overlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .45);
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            z-index: 1100;
        }

        .confirm-overlay.show {
            display: flex;
            animation: fo 120ms ease-out;
        }

        @keyframes fo {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        .confirm-dialog {
            width: min(520px, calc(100% - 56px));
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .35);
            padding: 18px;
            color: var(--text);
            transform: scale(.98);
            animation: pop 160ms cubic-bezier(.2, .8, .2, 1) both;
        }

        @keyframes pop {
            from {
                transform: scale(.96) translateY(8px);
                opacity: 0
            }

            to {
                transform: scale(1) translateY(0);
                opacity: 1
            }
        }

        .confirm-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .confirm-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, .12);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, .35);
        }

        [data-theme="dark"] .confirm-icon {
            background: rgba(239, 68, 68, .18);
        }

        .confirm-title {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.01em;
        }

        .confirm-body {
            color: var(--muted);
            line-height: 1.5;
            margin: 6px 0 14px;
        }

        .confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .confirm-actions .btn {
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 700;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
            cursor: pointer;
        }

        .confirm-actions .btn:hover {
            border-color: var(--accent);
        }

        .confirm-actions .btn-danger {
            background: #ef4444;
            border-color: #dc2626;
            color: #fff;
        }

        .confirm-actions .btn-danger:hover {
            filter: brightness(1.05);
        }
    </style>
    <div class="confirm-overlay" id="confirmOverlay" role="dialog" aria-modal="true" aria-labelledby="confirmTitle"
        aria-describedby="confirmDesc">
        <div class="confirm-dialog">
            <div class="confirm-head">
                <div class="confirm-icon"><i class="fas fa-trash"></i></div>
                <h3 class="confirm-title" id="confirmTitle">Delete product?</h3>
            </div>
            <div class="confirm-body" id="confirmDesc">
                This will permanently remove "<strong id="confirmName"></strong>" including all its variants, stock, and
                related data. This action cannot be undone.
            </div>
            <div class="confirm-actions">
                <button type="button" class="btn" id="confirmCancelBtn">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
    <script src="../Assets/Js/sidebar.js"></script>
    <script src="../Assets/Js/admin.js?v=20251018"></script>
    <script>
        (function () {
            var filter = document.getElementById('tableFilter');
            var lowOnly = document.getElementById('lowStockOnly');
            var rows = Array.from(document.querySelectorAll('tbody tr'));
            function norm(s) { return (s || '').toLowerCase(); }
            function apply() {
                var q = norm(filter ? filter.value : '');
                var low = lowOnly && lowOnly.checked;
                rows.forEach(function (tr) {
                    var text = norm(tr.innerText);
                    var stock = parseInt(tr.getAttribute('data-stock') || '0', 10);
                    var ok = (!q || text.indexOf(q) !== -1) && (!low || stock < 5);
                    tr.style.display = ok ? '' : 'none';
                });
            }
            if (filter) { filter.addEventListener('input', apply); }
            if (lowOnly) { lowOnly.addEventListener('change', apply); }
        })();
        // Modern confirm dialog wiring
        (function () {
            var overlay = document.getElementById('confirmOverlay');
            var nameEl = document.getElementById('confirmName');
            var btnCancel = document.getElementById('confirmCancelBtn');
            var btnDelete = document.getElementById('confirmDeleteBtn');
            var pendingHref = null;

            function open(name, href) {
                pendingHref = href;
                if (nameEl) nameEl.textContent = name || 'this product';
                if (overlay) { overlay.classList.add('show'); }
            }
            function close() { if (overlay) { overlay.classList.remove('show'); } pendingHref = null; }

            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(); } });
            if (overlay) overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
            if (btnCancel) btnCancel.addEventListener('click', close);
            if (btnDelete) btnDelete.addEventListener('click', function () { if (pendingHref) { window.location.href = pendingHref; } });

            // Hook delete buttons
            document.addEventListener('click', function (e) {
                var a = e.target.closest('a.js-del');
                if (!a) return;
                e.preventDefault();
                e.stopPropagation();
                var pname = a.getAttribute('data-name') || 'this product';
                open(pname, a.getAttribute('href'));
            });
        })();
    </script>
</body>

</html>