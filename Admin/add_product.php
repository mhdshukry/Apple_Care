<?php
include 'auth.php';
include '../config.php';
$current_page = "products";

// Handle product creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic fields
    $name = trim($_POST['name'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int) ($_POST['category'] ?? 0);
    $weight_value = trim($_POST['weight_value'] ?? '');
    $warranty_period = trim($_POST['warranty_period'] ?? '');
    $warranty_details = trim($_POST['warranty_details'] ?? '');
    $battery_life = trim($_POST['battery_life'] ?? '');
    // Offers removed

    // Arrays
    $storage_labels = $_POST['storage_label'] ?? [];
    $storage_prices = $_POST['storage_price'] ?? [];
    $storage_qtys = $_POST['storage_qty'] ?? [];

    $spec_names = $_POST['spec_name'] ?? [];
    $spec_values = $_POST['spec_value'] ?? [];

    $conn_types = $_POST['conn_type'] ?? [];
    $conn_details = $_POST['conn_details'] ?? [];

    $dim_names = $_POST['dimension_name'] ?? [];
    $dim_values = $_POST['dimension_value'] ?? [];

    $color_ids = $_POST['color_ids'] ?? [];

    $upload_dir = realpath(__DIR__ . '/../upload');
    if ($upload_dir === false) {
        @mkdir(__DIR__ . '/../upload', 0777, true);
        $upload_dir = realpath(__DIR__ . '/../upload');
    }
    $main_image_path = '';

    // Handle main image upload (required)
    if (!empty($_FILES['image_url']['name'])) {
        if (!empty($_FILES['image_url']['size']) && (int) $_FILES['image_url']['size'] > 5 * 1024 * 1024) {
            die('Image too large (max 5MB).');
        }
        $fname = basename($_FILES['image_url']['name']);
        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            die('Invalid image type. Allowed: jpg, jpeg, png, webp');
        }
        $safe = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', pathinfo($fname, PATHINFO_FILENAME));
        $newName = $safe . '_' . time() . '.' . $ext;
        $target = $upload_dir . DIRECTORY_SEPARATOR . $newName;
        if (!move_uploaded_file($_FILES['image_url']['tmp_name'], $target)) {
            die('Failed to upload image. Please check directory permissions.');
        }
        $main_image_path = '../upload/' . $newName;
    } else {
        die('Main image is required.');
    }

    $conn->begin_transaction();
    try {
        // Insert product (stock will be updated from storage_qtys)
        $stock_init = 0;
        $sql = "INSERT INTO products (name, model, stock, description, image_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssiss', $name, $model, $stock_init, $description, $main_image_path);
        $stmt->execute();
        $product_id = $stmt->insert_id;
        $stmt->close();

        // Category
        if ($category_id > 0) {
            $stmt = $conn->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $product_id, $category_id);
            $stmt->execute();
            $stmt->close();
        }

        // Storage options and per-storage stock
        $totalStock = 0;
        for ($i = 0; $i < count($storage_labels); $i++) {
            $label = trim($storage_labels[$i] ?? '');
            if ($label === '')
                continue;
            $price = isset($storage_prices[$i]) ? (float) $storage_prices[$i] : 0.0;
            $qty = isset($storage_qtys[$i]) ? (int) $storage_qtys[$i] : 0;

            $stmt = $conn->prepare("INSERT INTO storage_options (product_id, storage, price) VALUES (?, ?, ?)");
            $stmt->bind_param('isd', $product_id, $label, $price);
            $stmt->execute();
            $storage_id = $stmt->insert_id;
            $stmt->close();

            // storage stock
            $stmt = $conn->prepare("INSERT INTO storage_stock (product_id, storage_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param('iii', $product_id, $storage_id, $qty);
            $stmt->execute();
            $stmt->close();
            $totalStock += max(0, $qty);
        }

        // Update total product stock
        $stmt = $conn->prepare("UPDATE products SET stock=? WHERE product_id=?");
        $stmt->bind_param('ii', $totalStock, $product_id);
        $stmt->execute();
        $stmt->close();

        // Weight
        if ($weight_value !== '') {
            $stmt = $conn->prepare("INSERT INTO product_weight (product_id, weight_value) VALUES (?, ?)");
            $stmt->bind_param('is', $product_id, $weight_value);
            $stmt->execute();
            $stmt->close();
        }

        // Warranty
        if ($warranty_period !== '' || $warranty_details !== '') {
            $stmt = $conn->prepare("INSERT INTO product_warranties (product_id, warranty_period, warranty_details) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $product_id, $warranty_period, $warranty_details);
            $stmt->execute();
            $stmt->close();
        }

        // Specs
        for ($i = 0; $i < count($spec_names); $i++) {
            $sn = trim($spec_names[$i] ?? '');
            $sv = trim($spec_values[$i] ?? '');
            if ($sn === '' && $sv === '')
                continue;
            $stmt = $conn->prepare("INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $product_id, $sn, $sv);
            $stmt->execute();
            $stmt->close();
        }

        // Dimensions
        for ($i = 0; $i < count($dim_names); $i++) {
            $dn = trim($dim_names[$i] ?? '');
            $dv = trim($dim_values[$i] ?? '');
            if ($dn === '' && $dv === '')
                continue;
            $stmt = $conn->prepare("INSERT INTO product_dimensions (product_id, dimension_name, dimension_value) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $product_id, $dn, $dv);
            $stmt->execute();
            $stmt->close();
        }

        // Connectivity (use column name 'details' to match schema)
        for ($i = 0; $i < count($conn_types); $i++) {
            $ct = trim($conn_types[$i] ?? '');
            $cd = trim($conn_details[$i] ?? '');
            if ($ct === '' && $cd === '')
                continue;
            $stmt = $conn->prepare("INSERT INTO product_connectivity (product_id, connectivity_type, details) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $product_id, $ct, $cd);
            $stmt->execute();
            $stmt->close();
        }

        // Colors (many-to-many via product_color)
        foreach ($color_ids as $cid) {
            $cid = (int) $cid;
            if ($cid <= 0)
                continue;
            $stmt = $conn->prepare("INSERT INTO product_color (product_id, color_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $product_id, $cid);
            $stmt->execute();
            $stmt->close();
        }

        // Battery life
        if ($battery_life !== '') {
            $stmt = $conn->prepare("INSERT INTO product_battery_life (product_id, battery_life) VALUES (?, ?)");
            $stmt->bind_param('is', $product_id, $battery_life);
            $stmt->execute();
            $stmt->close();
        }

        // Product image record for the main image
        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
        $stmt->bind_param('is', $product_id, $main_image_path);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        header('Location: products_admin.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
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

<style>
    /* Sticky save bar styling aligned with 240px sidebar */
    .sticky-savebar {
        position: fixed;
        left: calc(240px + 20px);
        right: 20px;
        bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.12);
        z-index: 900;
    }

    /* Reserve space at the bottom for the sticky bar */
    .main-content {
        padding-bottom: 140px;
    }

    @media (max-width: 900px) {
        .sticky-savebar {
            left: calc(240px + 20px);
            right: 16px;
        }
    }

    /* Reduce visual empty space on this page */
    body.editor-modern {
        background: var(--bg);
    }

    /* Form-friendly grids overriding dashboard 3-col default */
    .ap-form {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 12px;
        margin: 8px 0 12px;
    }

    .ap-form.equal {
        grid-template-columns: 1fr 1fr;
    }

    .ap-form.one {
        grid-template-columns: 1fr;
    }

    /* Default spacer height in case JS is disabled or delayed */
    .savebar-spacer {
        height: 160px;
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
                <li class="<?php echo ($current_page == 'products') ? 'active' : ''; ?>">
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
                    <li><a href="#" class="social-media-link"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#" class="social-media-link"><i class="fab fa-facebook"></i></a></li>
                    <li><a href="#" class="social-media-link"><i class="fab fa-linkedin"></i></a></li>
                    <li><a href="#" class="social-media-link"><i class="fab fa-instagram"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="main-content admin-dashboard">
            <div class="topbar">
                <form class="search" action="#" onsubmit="return false;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Quick search…" />
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
                    <h1>Add Product</h1>
                    <p class="muted">Create a new product with variants, specs and details</p>
                </div>
                <div class="adm-actions">
                    <a href="products_admin.php" class="btn btn-small">Back to list</a>
                </div>
                <thead>
                    <tr>
                        <th>Storage</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th></th>
                    </tr>
                </thead>
                class="fas fa-mobile-alt"></i></span><input type="text" name="model" required>
        </div>

        <label>Category</label>
        <div class="input-group"><span class="input-prefix"><i class="fas fa-layer-group"></i></span>
            <select name="category" required>
                <option value="" disabled selected>Select category</option>
                <option value="1">Iphone</option>
                <option value="2">MacBook</option>
                <option value="3">Wearables</option>
                <option value="4">Accessories</option>
            </select>
        </div>
        <label>Description</label>
        <textarea name="description" rows="4" placeholder="Short description…"></textarea>

        <label>Main Image</label>
        <div class="dropzone" id="dzMain" tabindex="0">
            <div class="dz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="dz-hint">Drag & drop or click to upload</div>
            <input type="file" name="image_url" id="image_url" accept="image/*" required style="display:none" />
        </div>
        <div class="helper">Accepted: JPG, PNG, WEBP</div>
    </div>

    <div class="panel">
        <h3>Storage & Price</h3>
        <div class="helper">Add one or more storage variants with price and initial stock</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Storage</th>
                        <th>Price (Rs)</th>
                        <th>Qty</th>

                        <th></th>
                    </tr>
                </thead>
                <tbody id="storageRows">
                    <tr>
                        <td><input type="text" name="storage_label[]" placeholder="128GB" required></td>
                        <td><input type="number" step="0.01" name="storage_price[]" placeholder="0.00" required></td>
                        <td><input type="number" name="storage_qty[]" value="0" min="0"></td>

                        <td><button type="button" class="btn btn-small" onclick="addStorageRow()">Add</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="helper">Total stock: <strong id="totalStock">0</strong></div>
    </div>
    </section>

    <section class="panels ap-form equal">
        <div class="panel">
            <h3>Colors</h3>
            <div class="swatches">
                <?php
                $sql_colors = "SELECT color_id, color_name, color_hex FROM colors";
                $result_colors = $conn->query($sql_colors);
                if ($result_colors) {
                    while ($row = $result_colors->fetch_assoc()) {
                        $cid = (int) $row['color_id'];
                        $name = htmlspecialchars($row['color_name']);
                        $hex = htmlspecialchars($row['color_hex']);
                        echo '<label class="swatch" title="' . $name . '" style="--c: ' . $hex . '">';
                        echo '<input type="checkbox" name="color_ids[]" value="' . $cid . '" style="position:absolute;opacity:0;width:0;height:0">';
                        echo '</label>';
                    }
                }
                ?>
            </div>
        </div>
        <div class="panel">
            <h3>Weight & Warranty</h3>
            <label>Weight</label>
            <div class="input-group"><span class="input-prefix"><i class="fas fa-balance-scale"></i></span><input
                    type="text" name="weight_value" placeholder="e.g., 187 g"></div>
            <label>Warranty Period</label>
            <div class="input-group"><span class="input-prefix"><i class="fas fa-shield-alt"></i></span><input
                    type="text" name="warranty_period" placeholder="e.g., 1 Year"></div>
            <label>Warranty Details</label>
            <textarea name="warranty_details" rows="3"
                placeholder="e.g., Manufacturer warranty covering defects…"></textarea>
            <label>Battery Life</label>
            <div class="input-group"><span class="input-prefix"><i class="fas fa-battery-full"></i></span><input
                    type="text" name="battery_life" placeholder="e.g., Up to 20 hours"></div>
        </div>
    </section>

    <section class="panels ap-form equal">
        <div class="panel">
            <h3>Specifications</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Value</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="specRows">
                        <tr>
                            <td><input type="text" name="spec_name[]" placeholder="Chip"></td>
                            <td><input type="text" name="spec_value[]" placeholder="A16 Bionic"></td>
                            <td><button type="button" class="btn btn-small" onclick="addSpecRow()">Add</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel">
            <h3>Connectivity</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Details</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="connRows">
                        <tr>
                            <td><input type="text" name="conn_type[]" placeholder="Wi‑Fi"></td>
                            <td><input type="text" name="conn_details[]" placeholder="Wi‑Fi 6, Bluetooth 5.3"></td>
                            <td><button type="button" class="btn btn-small" onclick="addConnRow()">Add</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="panels ap-form one">
        <div class="panel">
            <h3>Dimensions</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Value</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="dimRows">
                        <tr>
                            <td><input type="text" name="dimension_name[]" placeholder="Height"></td>
                            <td><input type="text" name="dimension_value[]" placeholder="146.7 mm"></td>
                            <td><button type="button" class="btn btn-small" onclick="addDimRow()">Add</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Spacer so sticky save bar never covers the last section -->
    <div class="savebar-spacer" aria-hidden="true"></div>

    <!-- Sticky save bar -->
    <div class="sticky-savebar" role="region" aria-label="Save new product">
        <div style="display:flex;align-items:center;gap:10px;flex:1">
            <span class="pill">Total stock: <strong id="totalStock2">0</strong></span>
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-gradient"><i class="fas fa-save"></i> Create
                Product</button>
            <a href="products_admin.php" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
    </form>
    </div>
    </div>

    <script src="../Assets/Js/sidebar.js"></script>
    <script src="../Assets/Js/admin.js?v=20251018"></script>
    <script>
        // Dropzone behavior for main image
        (function () {
            var dz = document.getElementById('dzMain');
            if (!dz) return;
            var input = document.getElementById('image_url');
            function openPicker() { if (input) input.click(); }
            dz.addEventListener('click', openPicker);
            dz.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPicker(); } });
            dz.addEventListener('dragover', function (e) { e.preventDefault(); dz.classList.add('dragover'); });
            dz.addEventListener('dragleave', function () { dz.classList.remove('dragover'); });
            dz.addEventListener('drop', function (e) { e.preventDefault(); dz.classList.remove('dragover'); if (e.dataTransfer.files && e.dataTransfer.files[0]) { input.files = e.dataTransfer.files; } });
        })();

        // Dynamic rows add/remove
        function addRow(tbodyId, names) {
            var tbody = document.getElementById(tbodyId);
            if (!tbody) return;
            var tr = document.createElement('tr');
            var cells = names.map(function (n) { return '<td><input type="text" name="' + n + '[]"></td>'; }).join('');
            tr.innerHTML = cells + '<td><button type="button" class="btn btn-small" onclick="this.closest(\'tr\').remove(); calcTotal()">Remove</button></td>';
            tbody.appendChild(tr);
        }
        function addStorageRow() {
            var tbody = document.getElementById('storageRows');
            var tr = document.createElement('tr');
            tr.innerHTML = '<td><input type="text" name="storage_label[]" placeholder="256GB"></td>' +
                '<td><input type="number" step="0.01" name="storage_price[]" placeholder="0.00"></td>' +
                '<td><input type="number" name="storage_qty[]" value="0" min="0"></td>' +
                '<td><button type="button" class="btn btn-small" onclick="this.closest(\'tr\').remove(); calcTotal()">Remove</button></td>';
            tbody.appendChild(tr);
        }
        function addSpecRow() { addRow('specRows', ['spec_name', 'spec_value']); }
        function addConnRow() { addRow('connRows', ['conn_type', 'conn_details']); }
        function addDimRow() { addRow('dimRows', ['dimension_name', 'dimension_value']); }

        // Total stock calculation
        function calcTotal() {
            var total = 0;
            document.querySelectorAll('input[name="storage_qty[]"]').forEach(function (inp) { var v = parseInt(inp.value || '0', 10); if (!isNaN(v)) total += v; });
            var a = document.getElementById('totalStock'); if (a) a.textContent = total;
            var b = document.getElementById('totalStock2'); if (b) b.textContent = total;
        }
        document.addEventListener('input', function (e) { if (e.target && e.target.name === 'storage_qty[]') { calcTotal(); } });
        calcTotal();
        // Adjust bottom padding dynamically to exact sticky bar height (robust)
        (function () {
            var sb = document.querySelector('.sticky-savebar');
            var mc = document.querySelector('.main-content');
            var sp = document.querySelector('.savebar-spacer');
            if (!sb || !mc) return;
            function adjust() {
                var h = sb.offsetHeight + 32;
                mc.style.paddingBottom = h + 'px';
                if (sp) sp.style.height = h + 'px';
            }
            // initial and delayed adjustments to account for fonts/layout reflow
            adjust();
            setTimeout(adjust, 200);
            setTimeout(adjust, 600);
            window.addEventListener('load', adjust);
            window.addEventListener('resize', adjust);
            if (window.ResizeObserver) { try { new ResizeObserver(adjust).observe(sb); } catch (e) { } }
        })();
    </script>
</body>

</html>