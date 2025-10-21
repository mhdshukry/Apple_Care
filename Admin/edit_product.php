<?php
include 'auth.php';
include '../config.php';
// mark the correct sidebar state
$current_page = 'products';

// Initialize
$storage_options = [];
$existing_image_url = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs (avoid deprecated FILTER_SANITIZE_STRING)
    $product_id = (int) ($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $model = trim($_POST['model'] ?? '');
    // Product-level stock will be computed as the sum of per-storage quantities
    $stock = 0; // initialize; computed later from $storage_qtys
    $description = trim($_POST['description'] ?? '');
    $category_id = isset($_POST['category']) ? (int) $_POST['category'] : 0;
    // New structured inputs
    $posted_colors = isset($_POST['colors']) && is_array($_POST['colors']) ? array_map('intval', $_POST['colors']) : [];
    $storage_labels = isset($_POST['storage_label']) && is_array($_POST['storage_label']) ? $_POST['storage_label'] : [];
    $storage_prices = isset($_POST['storage_price']) && is_array($_POST['storage_price']) ? $_POST['storage_price'] : [];
    $storage_qtys = isset($_POST['storage_qty']) && is_array($_POST['storage_qty']) ? $_POST['storage_qty'] : [];
    $spec_names = isset($_POST['spec_name']) && is_array($_POST['spec_name']) ? $_POST['spec_name'] : [];
    $spec_values = isset($_POST['spec_value']) && is_array($_POST['spec_value']) ? $_POST['spec_value'] : [];
    $conn_types = isset($_POST['connectivity_type']) && is_array($_POST['connectivity_type']) ? $_POST['connectivity_type'] : [];
    // Offers removed
    $conn_details = isset($_POST['connectivity_details']) && is_array($_POST['connectivity_details']) ? $_POST['connectivity_details'] : [];
    $dim_names = isset($_POST['dimension_name']) && is_array($_POST['dimension_name']) ? $_POST['dimension_name'] : [];
    $dim_values = isset($_POST['dimension_value']) && is_array($_POST['dimension_value']) ? $_POST['dimension_value'] : [];
    $war_periods = isset($_POST['warranty_period']) && is_array($_POST['warranty_period']) ? $_POST['warranty_period'] : [];
    $war_details = isset($_POST['warranty_details']) && is_array($_POST['warranty_details']) ? $_POST['warranty_details'] : [];
    $weight_value = trim($_POST['weight_value'] ?? '');
    $upload_dir = realpath(__DIR__ . '/../upload') ?: (__DIR__ . '/../upload');
    $image_url = $_POST['existing_image_url'] ?? '';

    if (empty($product_id)) {
        die('Product ID is required.');
    }

    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0777, true);
    }

    // Handle image upload if provided
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
        // Reject files over ~5MB
        if (!empty($_FILES['image_url']['size']) && (int) $_FILES['image_url']['size'] > 5 * 1024 * 1024) {
            die('Image too large (max 5MB).');
        }
        $file_tmp = $_FILES['image_url']['tmp_name'];
        // Prefer finfo when available
        $mime = function_exists('finfo_open') ? (function ($f) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $m = finfo_file($fi, $f);
            finfo_close($fi);
            return $m; })($file_tmp) : mime_content_type($file_tmp);
        $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        if (!isset($allowed_types[$mime])) {
            die('Only JPG, PNG, and GIF files are allowed.');
        }
        $ext = $allowed_types[$mime];
        $safeBase = preg_replace('/[^a-zA-Z0-9-_]/', '_', pathinfo($_FILES['image_url']['name'], PATHINFO_FILENAME));
        $newName = $safeBase . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $targetPath = rtrim($upload_dir, '/\\') . DIRECTORY_SEPARATOR . $newName;
        if (!move_uploaded_file($file_tmp, $targetPath)) {
            die('Failed to upload image. Please check directory permissions.');
        }
        // Store web path relative to this page
        $image_url = '../upload/' . $newName;
    }

    $conn->begin_transaction();
    try {
        // Update product details (stock will be computed below after parsing storage quantities)
        $stmt = $conn->prepare("UPDATE products SET name = ?, model = ?, description = ?, image_url = ? WHERE product_id = ?");
        $stmt->bind_param("ssssi", $name, $model, $description, $image_url, $product_id);
        $stmt->execute();
        $stmt->close();

        // Colors: sync product_color to posted selections
        // Fetch current
        $current_colors = [];
        $res = $conn->prepare("SELECT color_id FROM product_color WHERE product_id = ?");
        $res->bind_param("i", $product_id);
        $res->execute();
        $rc = $res->get_result();
        while ($r = $rc->fetch_assoc()) {
            $current_colors[] = (int) $r['color_id'];
        }
        $res->close();
        $to_add = array_diff($posted_colors, $current_colors);
        $to_del = array_diff($current_colors, $posted_colors);
        if (!empty($to_add)) {
            $stmt = $conn->prepare("INSERT INTO product_color (product_id, color_id) VALUES (?, ?)");
            foreach ($to_add as $cid) {
                $cid = (int) $cid;
                $stmt->bind_param("ii", $product_id, $cid);
                $stmt->execute();
            }
            $stmt->close();
        }
        if (!empty($to_del)) {
            // Delete per-id using prepared statements
            $del = $conn->prepare("DELETE FROM product_color WHERE product_id = ? AND color_id = ?");
            foreach ($to_del as $cid) {
                $cid = (int) $cid;
                $del->bind_param("ii", $product_id, $cid);
                $del->execute();
            }
            $del->close();
        }
        // Storage options: upsert by storage label with price and stock qty
        $current_storage = [];// map storage label => [id, qty]
        $stmt = $conn->prepare("SELECT so.storage_id, so.storage, COALESCE(ss.quantity,0) AS qty FROM storage_options so LEFT JOIN storage_stock ss ON so.storage_id = ss.storage_id AND ss.product_id = so.product_id WHERE so.product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $cr = $stmt->get_result();
        while ($row = $cr->fetch_assoc()) {
            $current_storage[$row['storage']] = ['id' => (int) $row['storage_id'], 'qty' => (int) $row['qty']];
        }
        $stmt->close();

        $posted_labels = [];
        $computed_total_stock = 0;
        for ($i = 0; $i < count($storage_labels); $i++) {
            $label = trim((string) $storage_labels[$i]);
            if ($label === '')
                continue;
            $posted_labels[] = $label;
            $price_i = isset($storage_prices[$i]) ? (float) $storage_prices[$i] : 0.0;
            $qty_i = isset($storage_qtys[$i]) ? (int) $storage_qtys[$i] : 0;
            if ($qty_i < 0) {
                $qty_i = 0;
            }
            $computed_total_stock += $qty_i;
            if (isset($current_storage[$label])) {
                $sid = (int) $current_storage[$label]['id'];
                // update price only
                $u = $conn->prepare("UPDATE storage_options SET price = ? WHERE storage_id = ?");
                $u->bind_param("di", $price_i, $sid);
                $u->execute();
                $u->close();
                // upsert stock
                $chk = $conn->prepare("SELECT COUNT(*) FROM storage_stock WHERE storage_id = ? AND product_id = ?");
                $chk->bind_param("ii", $sid, $product_id);
                $chk->execute();
                $chk->bind_result($cnt);
                $chk->fetch();
                $chk->close();
                if ($cnt > 0) {
                    $us = $conn->prepare("UPDATE storage_stock SET quantity = ? WHERE storage_id = ? AND product_id = ?");
                    $us->bind_param("iii", $qty_i, $sid, $product_id);
                    $us->execute();
                    $us->close();
                } else {
                    $is = $conn->prepare("INSERT INTO storage_stock (product_id, storage_id, quantity) VALUES (?, ?, ?)");
                    $is->bind_param("iii", $product_id, $sid, $qty_i);
                    $is->execute();
                    $is->close();
                }
            } else {
                // insert storage option
                $ins = $conn->prepare("INSERT INTO storage_options (product_id, storage, price) VALUES (?, ?, ?)");
                $ins->bind_param("isd", $product_id, $label, $price_i);
                $ins->execute();
                $new_sid = $ins->insert_id;
                $ins->close();
                // insert stock
                $is = $conn->prepare("INSERT INTO storage_stock (product_id, storage_id, quantity) VALUES (?, ?, ?)");
                $is->bind_param("iii", $product_id, $new_sid, $qty_i);
                $is->execute();
                $is->close();
            }
        }
        // After syncing storages/stock, update the aggregated product stock
        $upd = $conn->prepare("UPDATE products SET stock = ? WHERE product_id = ?");
        $upd->bind_param("ii", $computed_total_stock, $product_id);
        $upd->execute();
        $upd->close();
        // Deletions for storage not posted (if not referenced)
        $to_remove = array_diff(array_keys($current_storage), $posted_labels);
        if (!empty($to_remove)) {
            // map labels to ids
            $ids = [];
            foreach ($to_remove as $lbl) {
                $ids[] = (int) $current_storage[$lbl]['id'];
            }
            // Check any order_details reference exists
            $totalRef = 0;
            $chk = $conn->prepare("SELECT COUNT(*) FROM order_details WHERE storage_id = ?");
            foreach ($ids as $sid) {
                $chk->bind_param("i", $sid);
                $chk->execute();
                $chk->bind_result($c);
                if ($chk->fetch()) {
                    $totalRef += (int) $c;
                }
                $chk->free_result();
            }
            $chk->close();
            if ($totalRef === 0) {
                // delete stock then options per id
                $ds = $conn->prepare("DELETE FROM storage_stock WHERE product_id = ? AND storage_id = ?");
                $do = $conn->prepare("DELETE FROM storage_options WHERE storage_id = ?");
                foreach ($ids as $sid) {
                    $ds->bind_param("ii", $product_id, $sid);
                    $ds->execute();
                    $do->bind_param("i", $sid);
                    $do->execute();
                }
                $ds->close();
                $do->close();
            }
        }

        // Specifications: reset and insert
        $qd = $conn->prepare("DELETE FROM product_specifications WHERE product_id = ?");
        $qd->bind_param("i", $product_id);
        $qd->execute();
        $qd->close();
        if (!empty($spec_names)) {
            $stmt = $conn->prepare("INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($spec_names); $i++) {
                $sn = trim((string) $spec_names[$i]);
                $sv = trim((string) $spec_values[$i] ?? '');
                if ($sn === '')
                    continue;
                $stmt->bind_param("iss", $product_id, $sn, $sv);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Connectivity
        $qd = $conn->prepare("DELETE FROM product_connectivity WHERE product_id = ?");
        $qd->bind_param("i", $product_id);
        $qd->execute();
        $qd->close();
        if (!empty($conn_types)) {
            $stmt = $conn->prepare("INSERT INTO product_connectivity (product_id, connectivity_type, details) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($conn_types); $i++) {
                $ct = trim((string) $conn_types[$i]);
                $cd = trim((string) ($conn_details[$i] ?? ''));
                if ($ct === '')
                    continue;
                $stmt->bind_param("iss", $product_id, $ct, $cd);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Dimensions
        $qd = $conn->prepare("DELETE FROM product_dimensions WHERE product_id = ?");
        $qd->bind_param("i", $product_id);
        $qd->execute();
        $qd->close();
        if (!empty($dim_names)) {
            $stmt = $conn->prepare("INSERT INTO product_dimensions (product_id, dimension_name, dimension_value) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($dim_names); $i++) {
                $dn = trim((string) $dim_names[$i]);
                $dv = trim((string) ($dim_values[$i] ?? ''));
                if ($dn === '')
                    continue;
                $stmt->bind_param("iss", $product_id, $dn, $dv);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Weight (single)
        $qd = $conn->prepare("DELETE FROM product_weight WHERE product_id = ?");
        $qd->bind_param("i", $product_id);
        $qd->execute();
        $qd->close();
        if ($weight_value !== '') {
            $stmt = $conn->prepare("INSERT INTO product_weight (product_id, weight_value) VALUES (?, ?)");
            $stmt->bind_param("is", $product_id, $weight_value);
            $stmt->execute();
            $stmt->close();
        }

        // Warranties
        $qd = $conn->prepare("DELETE FROM product_warranties WHERE product_id = ?");
        $qd->bind_param("i", $product_id);
        $qd->execute();
        $qd->close();
        if (!empty($war_periods)) {
            $stmt = $conn->prepare("INSERT INTO product_warranties (product_id, warranty_period, warranty_details) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($war_periods); $i++) {
                $wp = trim((string) $war_periods[$i]);
                $wd = trim((string) ($war_details[$i] ?? ''));
                if ($wp === '')
                    continue;
                $stmt->bind_param("iss", $product_id, $wp, $wd);
                $stmt->execute();
            }
            $stmt->close();
        }

        $conn->commit();
        header('Location: products_admin.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die('Error: ' . htmlspecialchars($e->getMessage()));
    }
} elseif (isset($_GET['id'])) {
    $product_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($product_id)) {
        echo "Product ID is required.";
        exit();
    }

    $stmt = $conn->prepare("SELECT p.*, c.category_id FROM products p JOIN product_categories c ON p.product_id = c.product_id WHERE p.product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT * FROM categories");
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();

    if ($categories->num_rows == 0) {
        echo "No categories found.";
        exit();
    }

    // Fetch storage options with price and stock
    $storages = [];
    $stmt = $conn->prepare("SELECT so.storage, so.price, so.storage_id, COALESCE(ss.quantity,0) AS qty FROM storage_options so LEFT JOIN storage_stock ss ON so.storage_id = ss.storage_id AND ss.product_id = so.product_id WHERE so.product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $storages[] = $row;
    }
    $stmt->close();

    // Fetch selectable colors and selected colors for the product
    $all_colors = [];
    $selected_color_ids = [];
    $res = $conn->query("SELECT color_id, color_name, color_hex FROM colors");
    if ($res) {
        while ($c = $res->fetch_assoc()) {
            $all_colors[] = $c;
        }
    }
    $res2 = $conn->prepare("SELECT color_id FROM product_color WHERE product_id = ?");
    $res2->bind_param("i", $product_id);
    $res2->execute();
    $rr = $res2->get_result();
    while ($c = $rr->fetch_assoc()) {
        $selected_color_ids[] = (int) $c['color_id'];
    }
    $res2->close();

    // Fetch additional data sets
    $specifications = [];
    $stmt = $conn->prepare("SELECT spec_name, spec_value FROM product_specifications WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $specifications[] = $row;
    }
    $stmt->close();

    $connectivities = [];
    $stmt = $conn->prepare("SELECT connectivity_type, details FROM product_connectivity WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $connectivities[] = $row;
    }
    $stmt->close();

    $dimensions = [];
    $stmt = $conn->prepare("SELECT dimension_name, dimension_value FROM product_dimensions WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $dimensions[] = $row;
    }
    $stmt->close();

    $warranties = [];
    $stmt = $conn->prepare("SELECT warranty_period, warranty_details FROM product_warranties WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $warranties[] = $row;
    }
    $stmt->close();

    $weight_value = '';
    $stmt = $conn->prepare("SELECT weight_value FROM product_weight WHERE product_id = ? LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($wv);
    if ($stmt->fetch()) {
        $weight_value = (string) $wv;
    }
    $stmt->close();

    $existing_image_url = $product['image_url'];

    if (!$product) {
        echo "Product not found.";
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+ - Edit Product</title>
    <link rel="icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="shortcut icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="stylesheet" href="../Assets/CSS/admin.css?v=20251018.2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap"
        rel="stylesheet">
    <style>
        /* Scoped modern editor styles */
        body.editor-modern .adm-header {
            background: linear-gradient(135deg, #111827 0%, #1f2937 50%, #0f172a 100%);
            color: #e5e7eb;
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.02);
            margin-bottom: 14px;
        }

        body.editor-modern .adm-header h1 {
            margin: 0;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        body.editor-modern .adm-header .muted {
            color: #cbd5e1;
        }

        /* Tabs */
        .edit-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 8px 0 16px;
        }

        .edit-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            cursor: pointer;
            border: 1px solid rgba(148, 163, 184, 0.3);
            color: #64748b;
            background: rgba(255, 255, 255, 0.6);
            transition: all .2s ease;
            user-select: none;
        }

        .dark .edit-tab {
            background: rgba(15, 23, 42, 0.5);
            color: #94a3b8;
            border-color: rgba(51, 65, 85, 0.6);
        }

        .edit-tab:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
        }

        .edit-tab.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 60%, #ec4899 100%);
            color: #fff;
            border-color: transparent;
        }

        .edit-tab i {
            font-size: 13px;
        }

        /* Panels per tab */
        .tab-section {
            display: none;
        }

        .tab-section.active {
            display: block;
            animation: fadeIn .18s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Make details accordion look like flat cards within tabs */
        .tab-section .acc {
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 14px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.6);
        }

        .dark .tab-section .acc {
            background: rgba(15, 23, 42, 0.5);
            border-color: rgba(51, 65, 85, 0.6);
        }

        .tab-section .acc summary {
            display: none;
        }

        .tab-section .acc .acc-body {
            padding: 0;
        }

        /* Media card polish */
        .gallery {
            position: sticky;
            top: 88px;
            align-self: start;
            border-radius: 16px;
            overflow: hidden;
        }

        .gallery .gallery-main {
            border-radius: 14px;
            overflow: hidden;
        }

        .dropzone {
            border-radius: 14px;
        }

        /* Layout variables */
        body.editor-modern {
            --savebar-h: 88px;
        }

        /* Make main content respect the fixed sidebar width and leave space for sticky bar */
        body.editor-modern .main-content {
            margin-left: 240px !important;
            padding-bottom: calc(var(--savebar-h, 88px) + 24px);
        }

        /* Sticky save bar (match Add Product) */
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

        .savebar-left {
            display: flex;
            align-items: center;
            gap: 14px;
            color: #334155;
        }

        .dark .savebar-left {
            color: #cbd5e1;
        }

        .savebar-left .badge {
            padding: 6px 10px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 12px;
            background: rgba(99, 102, 241, 0.12);
            color: #4338ca;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .dark .savebar-left .badge {
            background: rgba(99, 102, 241, 0.18);
            color: #a5b4fc;
            border-color: rgba(99, 102, 241, 0.4);
        }

        .savebar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(148, 163, 184, 0.5);
            color: #475569;
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .dark .btn-outline {
            color: #cbd5e1;
            border-color: rgba(51, 65, 85, 0.8);
        }

        .btn-outline:hover {
            background: rgba(148, 163, 184, 0.08);
        }

        .btn-gradient {
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35);
        }

        /* Tighten inputs a bit more for compact feel */
        body.editor-modern.compact input,
        body.editor-modern.compact select,
        body.editor-modern.compact textarea {
            height: 40px;
        }

        body.editor-modern.compact textarea {
            height: auto;
            min-height: 100px;
        }

        .table-wrap table th,
        .table-wrap table td {
            padding: 8px 10px;
        }

        /* Slight bottom spacing for the main panel */
        .panel {
            margin-bottom: 24px;
        }

        /* Tabs container should align with right column */
        .right-col-header {
            margin-bottom: 8px;
        }

        /* Responsive: keep content aligned to fixed sidebar */
        @media (max-width: 900px) {
            .sticky-savebar {
                left: calc(240px + 20px);
                right: 16px;
            }
        }

        /* Default spacer height as a safety fallback */
        .savebar-spacer {
            height: 160px;
        }
    </style>
</head>

<body class="editor-modern compact">
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
                    <h1><i class="fas fa-pen-nib" style="opacity:.9"></i> Edit Product</h1>
                    <div class="muted">Update product details, storages and media</div>
                </div>
                <div class="adm-actions">
                    <a href="products_admin.php" class="btn btn-secondary btn-small"><i class="fas fa-arrow-left"></i>
                        Back to catalog</a>
                </div>
            </header>

            <div class="panel">
                <form action="edit_product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id"
                        value="<?php echo htmlspecialchars($product['product_id']); ?>">
                    <input type="hidden" name="existing_image_url"
                        value="<?php echo htmlspecialchars($existing_image_url); ?>">

                    <div class="product-detail-grid">
                        <div class="gallery">
                            <div class="gallery-main" id="imgPreview">
                                <?php if (!empty($existing_image_url)): ?>
                                    <img src="<?php echo htmlspecialchars($existing_image_url); ?>" alt="Preview">
                                <?php else: ?>
                                    <div class="muted">No image selected</div>
                                <?php endif; ?>
                            </div>
                            <div class="dropzone" id="dropzone">
                                <div>
                                    <div class="dz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div>Drag & drop an image here or click to upload</div>
                                    <div class="dz-hint">JPG, PNG or GIF</div>
                                </div>
                                <input type="file" name="image_url" id="image_url" accept="image/*"
                                    style="display:none">
                            </div>
                            <span class="helper"
                                id="fileName"><?php echo $existing_image_url ? basename($existing_image_url) : ''; ?></span>
                        </div>
                        <div>
                            <div class="right-col-header">
                                <nav class="edit-tabs" role="tablist" aria-label="Edit product sections">
                                    <button type="button" class="edit-tab active" data-tab="basics"
                                        aria-selected="true"><i class="fas fa-sliders-h"></i> Basics</button>
                                    <button type="button" class="edit-tab" data-tab="storage"><i class="fas fa-hdd"></i>
                                        Storage & Price</button>
                                    <button type="button" class="edit-tab" data-tab="colors"><i
                                            class="fas fa-palette"></i> Colors</button>
                                    <button type="button" class="edit-tab" data-tab="description"><i
                                            class="fas fa-align-left"></i> Description</button>
                                    <button type="button" class="edit-tab" data-tab="specs"><i
                                            class="fas fa-list-ol"></i> Specs</button>
                                    <button type="button" class="edit-tab" data-tab="connectivity"><i
                                            class="fas fa-wifi"></i> Connectivity</button>
                                    <button type="button" class="edit-tab" data-tab="dimensions"><i
                                            class="fas fa-ruler-combined"></i> Dimensions</button>
                                    <button type="button" class="edit-tab" data-tab="warranty"><i
                                            class="fas fa-shield-alt"></i> Weight & Warranty</button>
                                </nav>
                            </div>

                            <section class="tab-section active" data-tab="basics">
                                <div class="pd-header">
                                    <label for="name">Product Name</label>
                                    <div class="input-group">
                                        <span class="input-prefix"><i class="fas fa-tag"></i></span>
                                        <input type="text" name="name" id="name" placeholder="e.g., iPhone 15 Pro"
                                            value="<?php echo htmlspecialchars(isset($product['name']) ? (string) $product['name'] : ''); ?>"
                                            required>
                                    </div>
                                </div>
                                <div class="acc" open>
                                    <div class="acc-body">
                                        <div class="pd-section" style="margin-top:0">
                                            <label for="model">Model</label>
                                            <div class="input-group">
                                                <span class="input-prefix"><i class="fas fa-barcode"></i></span>
                                                <input type="text" name="model" id="model" placeholder="e.g., A3100"
                                                    value="<?php echo htmlspecialchars(isset($product['model']) ? (string) $product['model'] : ''); ?>"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="pd-section">
                                            <label for="category">Category</label>
                                            <select name="category" id="category" required>
                                                <option value="">Select Category</option>
                                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($cat['category_id']); ?>"
                                                        <?php echo ((int) $cat['category_id'] === (int) $product['category_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) $cat['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                    </div>
                                </div>
                            </section>

                            <section class="tab-section" data-tab="storage">
                                <div class="acc" open>
                                    <div class="acc-body">
                                        <div class="pd-section" style="margin-top:0">
                                            <label>Storage Options (label, price, stock)</label>
                                            <div class="table-wrap"
                                                style="border:none;padding:0;background:transparent">
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th>Storage</th>
                                                            <th style="width:140px">Price (Rs)</th>
                                                            <th style="width:100px">Stock</th>

                                                            <th style="width:60px"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="storBody">
                                                        <?php if (!empty($storages)):
                                                            foreach ($storages as $st): ?>
                                                                <tr>
                                                                    <td><input type="text" name="storage_label[]"
                                                                            value="<?php echo htmlspecialchars($st['storage']); ?>"
                                                                            placeholder="e.g., 128GB"></td>
                                                                    <td><input type="number" step="0.01" name="storage_price[]"
                                                                            value="<?php echo htmlspecialchars($st['price']); ?>"
                                                                            placeholder="0.00"></td>
                                                                    <td><input type="number" name="storage_qty[]"
                                                                            value="<?php echo htmlspecialchars($st['qty']); ?>"
                                                                            placeholder="0"></td>

                                                                    <td><button type="button" class="icon-btn"
                                                                            onclick="this.closest('tr').remove(); window._recalcTotal && window._recalcTotal();"
                                                                            title="Remove row"><i
                                                                                class="fas fa-trash"></i></button></td>
                                                                </tr>
                                                            <?php endforeach; else: ?>
                                                            <tr>
                                                                <td><input type="text" name="storage_label[]"
                                                                        placeholder="e.g., 128GB"></td>
                                                                <td><input type="number" step="0.01" name="storage_price[]"
                                                                        placeholder="0.00"></td>
                                                                <td><input type="number" name="storage_qty[]"
                                                                        placeholder="0"></td>

                                                                <td><button type="button" class="icon-btn"
                                                                        onclick="this.closest('tr').remove(); window._recalcTotal && window._recalcTotal();"
                                                                        title="Remove row"><i
                                                                            class="fas fa-trash"></i></button></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="pd-actions">
                                                <button type="button" class="btn btn-secondary btn-small"
                                                    onclick="addStorRow(); window._wireStorInputs && window._wireStorInputs();"><i
                                                        class="fas fa-plus"></i> Add row</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="tab-section" data-tab="colors">
                                <div class="acc" open>
                                    <div class="acc-body">
                                        <div class="pd-section" style="margin-top:0">
                                            <label>Available Colors</label>
                                            <div class="pd-options">
                                                <?php foreach ($all_colors as $col):
                                                    $cid = (int) $col['color_id'];
                                                    $sel = in_array($cid, $selected_color_ids); ?>
                                                    <label class="pill <?php echo $sel ? 'active' : ''; ?>"
                                                        style="cursor:pointer;display:flex;align-items:center;gap:8px">
                                                        <span class="swatch"
                                                            style="--c: <?php echo htmlspecialchars($col['color_hex']); ?>; width:18px;height:18px"></span>
                                                        <input type="checkbox" name="colors[]" value="<?php echo $cid; ?>"
                                                            <?php echo $sel ? 'checked' : ''; ?>>
                                                        <?php echo htmlspecialchars($col['color_name']); ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="tab-section" data-tab="description">
                                <div class="acc" open>
                                    <div class="acc-body">
                                        <div class="pd-section">
                                            <label for="description">Description</label>
                                            <textarea name="description" id="description" rows="5"
                                                required><?php echo htmlspecialchars(isset($product['description']) ? (string) $product['description'] : ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="tab-section" data-tab="specs">
                                <div class="acc" open>
                                    <div class="acc-body">
                                        <div class="pd-section" style="margin-top:0">
                                            <label>Specifications</label>
                                            <div class="table-wrap"
                                                style="border:none;padding:0;background:transparent">
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th>Name</th>
                                                            <th>Value</th>
                                                            <th style="width:80px"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="specBody">
                                                        <?php if (!empty($specifications)):
                                                            foreach ($specifications as $sp): ?>
                                                                <tr>
                                                                    <td><input type="text" name="spec_name[]"
                                                                            value="<?php echo htmlspecialchars($sp['spec_name']); ?>"
                                                                            placeholder="e.g., Display"></td>
                                                                    <td><input type="text" name="spec_value[]"
                                                                            value="<?php echo htmlspecialchars($sp['spec_value']); ?>"
                                                                            placeholder="e.g., 6.1&quot; OLED"></td>
                                                                    <td><button type="button" class="icon-btn"
                                                                            onclick="this.closest('tr').remove()"><i
                                                                                class="fas fa-trash"></i></button></td>
                                                                </tr>
                                                            <?php endforeach; else: ?>
                                                            <tr>
                                                                <td><input type="text" name="spec_name[]"
                                                                        placeholder="e.g., Display"></td>
                                                                <td><input type="text" name="spec_value[]"
                                                                        placeholder="e.g., 6.1&quot; OLED"></td>
                                                                <td><button type="button" class="icon-btn"
                                                                        onclick="this.closest('tr').remove()"><i
                                                                            class="fas fa-trash"></i></button></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="pd-actions"><button type="button"
                                                    class="btn btn-secondary btn-small" onclick="addSpecRow()"><i
                                                        class="fas fa-plus"></i> Add spec</button></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="tab-section" data-tab="connectivity">
                                <div class="acc" open>
                                    <div class="acc-body">
                                        <div class="pd-section" style="margin-top:0">
                                            <label>Connectivity</label>
                                            <div class="table-wrap"
                                                style="border:none;padding:0;background:transparent">
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th>Type</th>
                                                            <th>Details</th>
                                                            <th style="width:80px"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="connBody">
                                                        <?php if (!empty($connectivities)):
                                                            foreach ($connectivities as $co): ?>
                                                                <tr>
                                                                    <td><input type="text" name="connectivity_type[]"
                                                                            value="<?php echo htmlspecialchars($co['connectivity_type']); ?>"
                                                                            placeholder="e.g., 5G"></td>
                                                                    <td><input type="text" name="connectivity_details[]"
                                                                            value="<?php echo htmlspecialchars($co['details']); ?>"
                                                                            placeholder="e.g., Sub-6, mmWave"></td>
                                                                    <td><button type="button" class="icon-btn"
                                                                            onclick="this.closest('tr').remove()"><i
                                                                                class="fas fa-trash"></i></button></td>
                                                                </tr>
                                                            <?php endforeach; else: ?>
                                                            <tr>
                                                                <td><input type="text" name="connectivity_type[]"
                                                                        placeholder="e.g., 5G"></td>
                                                                <td><input type="text" name="connectivity_details[]"
                                                                        placeholder="e.g., Sub-6, mmWave"></td>
                                                                <td><button type="button" class="icon-btn"
                                                                        onclick="this.closest('tr').remove()"><i
                                                                            class="fas fa-trash"></i></button></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="pd-actions"><button type="button"
                                                    class="btn btn-secondary btn-small" onclick="addConnRow()"><i
                                                        class="fas fa-plus"></i> Add connectivity</button></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="tab-section" data-tab="dimensions">
                                <div class="acc" open>
                                    <div class="acc-body">
                                        <div class="pd-section" style="margin-top:0">
                                            <label>Dimensions</label>
                                            <div class="table-wrap"
                                                style="border:none;padding:0;background:transparent">
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th>Name</th>
                                                            <th>Value</th>
                                                            <th style="width:80px"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="dimBody">
                                                        <?php if (!empty($dimensions)):
                                                            foreach ($dimensions as $dm): ?>
                                                                <tr>
                                                                    <td><input type="text" name="dimension_name[]"
                                                                            value="<?php echo htmlspecialchars($dm['dimension_name']); ?>"
                                                                            placeholder="e.g., Height"></td>
                                                                    <td><input type="text" name="dimension_value[]"
                                                                            value="<?php echo htmlspecialchars($dm['dimension_value']); ?>"
                                                                            placeholder="e.g., 146.6 mm"></td>
                                                                    <td><button type="button" class="icon-btn"
                                                                            onclick="this.closest('tr').remove()"><i
                                                                                class="fas fa-trash"></i></button></td>
                                                                </tr>
                                                            <?php endforeach; else: ?>
                                                            <tr>
                                                                <td><input type="text" name="dimension_name[]"
                                                                        placeholder="e.g., Height"></td>
                                                                <td><input type="text" name="dimension_value[]"
                                                                        placeholder="e.g., 146.6 mm"></td>
                                                                <td><button type="button" class="icon-btn"
                                                                        onclick="this.closest('tr').remove()"><i
                                                                            class="fas fa-trash"></i></button></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="pd-actions"><button type="button"
                                                    class="btn btn-secondary btn-small" onclick="addDimRow()"><i
                                                        class="fas fa-plus"></i> Add dimension</button></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="tab-section" data-tab="warranty">
                                <div class="acc" open>
                                    <div class="acc-body">
                                        <div class="pd-section" style="margin-top:0">
                                            <label>Weight</label>
                                            <div class="input-group">
                                                <span class="input-prefix">⚖️</span>
                                                <input type="text" name="weight_value"
                                                    value="<?php echo htmlspecialchars($weight_value); ?>"
                                                    placeholder="e.g., 187 g">
                                            </div>
                                        </div>
                                        <div class="pd-section">
                                            <label>Warranties</label>
                                            <div class="table-wrap"
                                                style="border:none;padding:0;background:transparent">
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th>Period</th>
                                                            <th>Details</th>
                                                            <th style="width:80px"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="warBody">
                                                        <?php if (!empty($warranties)):
                                                            foreach ($warranties as $wa): ?>
                                                                <tr>
                                                                    <td><input type="text" name="warranty_period[]"
                                                                            value="<?php echo htmlspecialchars($wa['warranty_period']); ?>"
                                                                            placeholder="e.g., 1 Year"></td>
                                                                    <td><input type="text" name="warranty_details[]"
                                                                            value="<?php echo htmlspecialchars($wa['warranty_details']); ?>"
                                                                            placeholder="e.g., Limited hardware warranty"></td>
                                                                    <td><button type="button" class="icon-btn"
                                                                            onclick="this.closest('tr').remove()"><i
                                                                                class="fas fa-trash"></i></button></td>
                                                                </tr>
                                                            <?php endforeach; else: ?>
                                                            <tr>
                                                                <td><input type="text" name="warranty_period[]"
                                                                        placeholder="e.g., 1 Year"></td>
                                                                <td><input type="text" name="warranty_details[]"
                                                                        placeholder="e.g., Limited hardware warranty"></td>
                                                                <td><button type="button" class="icon-btn"
                                                                        onclick="this.closest('tr').remove()"><i
                                                                            class="fas fa-trash"></i></button></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="pd-actions"><button type="button"
                                                    class="btn btn-secondary btn-small" onclick="addWarRow()"><i
                                                        class="fas fa-plus"></i> Add warranty</button></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Legacy bottom action removed; sticky save bar will handle submission -->
                        </div>
                    </div>

                    <!-- Spacer so sticky save bar never covers last fields -->
                    <div class="savebar-spacer" aria-hidden="true"></div>

                    <!-- Sticky save bar (matched to Add Product) -->
                    <div class="sticky-savebar" role="region" aria-label="Save changes bar">
                        <div style="display:flex;align-items:center;gap:10px;flex:1">
                            <span class="pill">Total stock: <strong id="totalStock2">0</strong></span>
                        </div>
                        <div style="display:flex;gap:10px">
                            <button type="submit" class="btn btn-gradient"><i class="fas fa-save"></i> Update
                                Product</button>
                            <a href="products_admin.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var input = document.getElementById('image_url');
            var previewWrap = document.getElementById('imgPreview');
            var fileName = document.getElementById('fileName');
            var drop = document.getElementById('dropzone');
            // Sidebar width is handled globally in admin.js via --sidebar-offset
            // Image preview + filename
            if (input) {
                input.addEventListener('change', function () {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var img = previewWrap.querySelector('img');
                            if (!img) { img = document.createElement('img'); previewWrap.innerHTML = ''; previewWrap.appendChild(img); }
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(input.files[0]);
                        if (fileName) { fileName.textContent = input.files[0].name; }
                    }
                });
            }
            // Dropzone behavior
            if (drop && input) {
                ['dragenter', 'dragover'].forEach(function (evt) {
                    drop.addEventListener(evt, function (e) { e.preventDefault(); e.stopPropagation(); drop.classList.add('dragover'); });
                });
                ['dragleave', 'drop'].forEach(function (evt) {
                    drop.addEventListener(evt, function (e) { e.preventDefault(); e.stopPropagation(); drop.classList.remove('dragover'); });
                });
                drop.addEventListener('click', function () { input.click(); });
                drop.addEventListener('drop', function (e) {
                    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                        input.files = e.dataTransfer.files;
                        var ev = new Event('change');
                        input.dispatchEvent(ev);
                    }
                });
            }
            // Toggle active style on storage pills
            var pills = document.querySelectorAll('.pd-options .pill');
            pills.forEach(function (p) {
                var cb = p.querySelector('input[type="checkbox"]');
                if (!cb) return;
                cb.addEventListener('change', function () {
                    if (cb.checked) p.classList.add('active'); else p.classList.remove('active');
                });
            });
            // Add row helpers
            window.addStorRow = function () {
                var tbody = document.getElementById('storBody');
                if (!tbody) return;
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><input type="text" name="storage_label[]" placeholder="e.g., 128GB"></td>' +
                    '<td><input type="number" step="0.01" name="storage_price[]" placeholder="0.00"></td>' +
                    '<td><input type="number" name="storage_qty[]" placeholder="0"></td>' +
                    '<td><button type="button" class="icon-btn" onclick="this.closest(\'tr\').remove(); window._recalcTotal && window._recalcTotal();" title="Remove row"><i class="fas fa-trash"></i></button></td>';
                tbody.appendChild(tr);
                window._wireStorInputs && window._wireStorInputs();
                window._recalcTotal && window._recalcTotal();
            };
            window.addSpecRow = function () {
                var tbody = document.getElementById('specBody');
                if (!tbody) return;
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><input type="text" name="spec_name[]" placeholder="e.g., Display"></td>' +
                    '<td><input type="text" name="spec_value[]" placeholder="e.g., 6.1&quot; OLED"></td>' +
                    '<td><button type="button" class="icon-btn" onclick="this.closest(\'tr\').remove()"><i class="fas fa-trash"></i></button></td>';
                tbody.appendChild(tr);
            };
            window.addConnRow = function () {
                var tbody = document.getElementById('connBody');
                if (!tbody) return;
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><input type="text" name="connectivity_type[]" placeholder="e.g., 5G"></td>' +
                    '<td><input type="text" name="connectivity_details[]" placeholder="e.g., Sub-6, mmWave"></td>' +
                    '<td><button type="button" class="icon-btn" onclick="this.closest(\'tr\').remove()"><i class="fas fa-trash"></i></button></td>';
                tbody.appendChild(tr);
            };
            window.addDimRow = function () {
                var tbody = document.getElementById('dimBody');
                if (!tbody) return;
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><input type="text" name="dimension_name[]" placeholder="e.g., Height"></td>' +
                    '<td><input type="text" name="dimension_value[]" placeholder="e.g., 146.6 mm"></td>' +
                    '<td><button type="button" class="icon-btn" onclick="this.closest(\'tr\').remove()"><i class="fas fa-trash"></i></button></td>';
                tbody.appendChild(tr);
            };
            window.addWarRow = function () {
                var tbody = document.getElementById('warBody');
                if (!tbody) return;
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><input type="text" name="warranty_period[]" placeholder="e.g., 1 Year"></td>' +
                    '<td><input type="text" name="warranty_details[]" placeholder="e.g., Limited hardware warranty"></td>' +
                    '<td><button type="button" class="icon-btn" onclick="this.closest(\'tr\').remove()"><i class="fas fa-trash"></i></button></td>';
                tbody.appendChild(tr);
            };

            // Tabs switching
            var tabButtons = document.querySelectorAll('.edit-tab');
            var tabSections = document.querySelectorAll('.tab-section');
            function switchTab(tab) {
                tabButtons.forEach(function (btn) { btn.classList.toggle('active', btn.getAttribute('data-tab') === tab); btn.setAttribute('aria-selected', btn.classList.contains('active') ? 'true' : 'false'); });
                tabSections.forEach(function (sec) { sec.classList.toggle('active', sec.getAttribute('data-tab') === tab); });
            }
            tabButtons.forEach(function (btn) {
                btn.addEventListener('click', function () { switchTab(btn.getAttribute('data-tab')); });
            });

            // Compute and set sticky save bar height and spacer to avoid overlap
            (function () {
                var savebar = document.querySelector('.sticky-savebar');
                var spacer = document.querySelector('.savebar-spacer');
                function applySavebarHeight() {
                    if (!savebar) return;
                    var h = savebar.getBoundingClientRect().height + 16; // include breathing space
                    document.body.style.setProperty('--savebar-h', h + 'px');
                    if (spacer) spacer.style.height = (h + 16) + 'px'; // extra space beneath content
                }
                applySavebarHeight();
                window.addEventListener('resize', applySavebarHeight);
                if (window.ResizeObserver && savebar) {
                    try { new ResizeObserver(applySavebarHeight).observe(savebar); } catch (e) { }
                }
                setTimeout(applySavebarHeight, 200);
                setTimeout(applySavebarHeight, 600);
            })();

            // Total stock calculator
            var totalStockEl = document.getElementById('totalStock');
            var totalStockEl2 = document.getElementById('totalStock2');
            window._recalcTotal = function () {
                var sum = 0;
                document.querySelectorAll('input[name="storage_qty[]"]').forEach(function (inp) {
                    var v = parseInt(inp.value, 10); if (!isNaN(v) && v > 0) sum += v;
                });
                if (totalStockEl) totalStockEl.textContent = sum;
                if (totalStockEl2) totalStockEl2.textContent = sum;
            };
            window._wireStorInputs = function () {
                document.querySelectorAll('input[name="storage_qty[]"]').forEach(function (inp) {
                    if (!inp._wired) {
                        inp.addEventListener('input', function () { window._recalcTotal(); });
                        inp._wired = true;
                    }
                });
            };
            window._wireStorInputs();
            window._recalcTotal();
        })();
    </script>
    <script src="../Assets/Js/sidebar.js"></script>
    <script src="../Assets/Js/admin.js?v=20251018"></script>
</body>

</html>