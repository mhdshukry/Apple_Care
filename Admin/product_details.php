<?php
include 'auth.php';
include '../config.php';

$current_page = 'admin';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$storage_id = isset($_GET['storage_id']) ? intval($_GET['storage_id']) : 0;

// Fetch product details
$sql = "SELECT p.*, s.storage, s.price
        FROM products p
        LEFT JOIN storage_options s ON p.product_id = s.product_id
        WHERE p.product_id = ? AND (s.storage_id = ? OR ? = 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $product_id, $storage_id, $storage_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// Fetch additional details
$images_sql = "SELECT * FROM product_images WHERE product_id = ?";
$images_stmt = $conn->prepare($images_sql);
$images_stmt->bind_param('i', $product_id);
$images_stmt->execute();
$images = $images_stmt->get_result();

// Fetch storage stocks with storage options
$stocks_sql = "SELECT ss.*, so.storage 
               FROM storage_stock ss
               JOIN storage_options so ON ss.storage_id = so.storage_id
               WHERE ss.product_id = ?";
$stocks_stmt = $conn->prepare($stocks_sql);
$stocks_stmt->bind_param('i', $product_id);
$stocks_stmt->execute();
$stocks = $stocks_stmt->get_result();

$battery_sql = "SELECT * FROM product_battery_life WHERE product_id = ?";
$battery_stmt = $conn->prepare($battery_sql);
$battery_stmt->bind_param('i', $product_id);
$battery_stmt->execute();
$battery_life = $battery_stmt->get_result()->fetch_assoc();

$categories_sql = "SELECT c.name FROM product_categories pc
                   JOIN categories c ON pc.category_id = c.category_id
                   WHERE pc.product_id = ?";
$categories_stmt = $conn->prepare($categories_sql);
$categories_stmt->bind_param('i', $product_id);
$categories_stmt->execute();
$categories = $categories_stmt->get_result();

$colors_sql = "SELECT c.color_name, c.color_hex FROM product_color pc
               JOIN colors c ON pc.color_id = c.color_id
               WHERE pc.product_id = ?";
$colors_stmt = $conn->prepare($colors_sql);
$colors_stmt->bind_param('i', $product_id);
$colors_stmt->execute();
$colors = $colors_stmt->get_result();

$connectivity_sql = "SELECT * FROM product_connectivity WHERE product_id = ?";
$connectivity_stmt = $conn->prepare($connectivity_sql);
$connectivity_stmt->bind_param('i', $product_id);
$connectivity_stmt->execute();
$connectivity = $connectivity_stmt->get_result();

$dimensions_sql = "SELECT * FROM product_dimensions WHERE product_id = ?";
$dimensions_stmt = $conn->prepare($dimensions_sql);
$dimensions_stmt->bind_param('i', $product_id);
$dimensions_stmt->execute();
$dimensions = $dimensions_stmt->get_result();

$specifications_sql = "SELECT * FROM product_specifications WHERE product_id = ?";
$specifications_stmt = $conn->prepare($specifications_sql);
$specifications_stmt->bind_param('i', $product_id);
$specifications_stmt->execute();
$specifications = $specifications_stmt->get_result();

$warranties_sql = "SELECT * FROM product_warranties WHERE product_id = ?";
$warranties_stmt = $conn->prepare($warranties_sql);
$warranties_stmt->bind_param('i', $product_id);
$warranties_stmt->execute();
$warranties = $warranties_stmt->get_result();

$weight_sql = "SELECT * FROM product_weight WHERE product_id = ?";
$weight_stmt = $conn->prepare($weight_sql);
$weight_stmt->bind_param('i', $product_id);
$weight_stmt->execute();
$weight = $weight_stmt->get_result()->fetch_assoc();

$storage_options_sql = "SELECT * FROM storage_options WHERE product_id = ?";
$storage_options_stmt = $conn->prepare($storage_options_sql);
$storage_options_stmt->bind_param('i', $product_id);
$storage_options_stmt->execute();
$storage_options = $storage_options_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+ - Product Details</title>
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
                        <?php echo htmlspecialchars($adminInitial); ?></div>
                </div>
            </div>
            <header class="adm-header">
                <div>
                    <h1>Product Details</h1>
                    <p class="muted">Full specs, variants, and stock</p>
                </div>
                <div class="adm-actions"></div>
            </header>

            <section class="product-detail-grid">
                <div class="panel">
                    <div class="gallery">
                        <div class="gallery-main">
                            <img id="mainImage" src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                alt="Product Image">
                        </div>
                        <div class="gallery-thumbs">
                            <img class="thumb" src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="thumb">
                            <?php while ($image = $images->fetch_assoc()): ?>
                                <img class="thumb" src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="thumb">
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <div class="pd-header">
                        <h2 class="pd-title"><?php echo htmlspecialchars($product['name']); ?></h2>
                        <div class="pd-meta chips">
                            <?php if ($battery_life): ?><span class="chip light">🔋
                                    <?php echo htmlspecialchars($battery_life['battery_life']); ?></span><?php endif; ?>
                            <?php if ($weight): ?><span class="chip light">⚖️
                                    <?php echo htmlspecialchars($weight['weight_value']); ?></span><?php endif; ?>
                        </div>
                        <?php if (isset($product['price']) && $product['price']): ?>
                            <div class="pd-price">Rs<?php echo number_format((float) $product['price'], 2); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="pd-section">
                        <h3>Storage Options</h3>
                        <div class="pd-options">
                            <?php
                            $storage_options_stmt->execute();
                            $storage_options = $storage_options_stmt->get_result();
                            while ($storage = $storage_options->fetch_assoc()): ?>
                                <span
                                    class="pill"><?php echo htmlspecialchars($storage['storage']) . ' · Rs' . number_format((float) $storage['price'], 2); ?></span>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="pd-section">
                        <h3>Colors</h3>
                        <div class="swatches">
                            <?php
                            $colors_stmt->execute();
                            $colors = $colors_stmt->get_result();
                            while ($color = $colors->fetch_assoc()): ?>
                                <span class="swatch" title="<?php echo htmlspecialchars($color['color_name']); ?>"
                                    style="--c: <?php echo htmlspecialchars($color['color_hex']); ?>"></span>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="pd-actions">
                        <a class="btn btn-gradient" href="edit_product.php?id=<?php echo (int) $product_id; ?>"><i
                                class="fas fa-edit"></i> Edit</a>
                        <a class="btn btn-secondary" href="products_admin.php"><i class="fas fa-arrow-left"></i> Back to
                            list</a>
                    </div>
                </div>
            </section>

            <section class="panels">
                <div class="panel">
                    <div class="panel-head">
                        <h3>Categories</h3>
                    </div>
                    <div class="chips">
                        <?php
                        $categories_stmt->execute();
                        $categories = $categories_stmt->get_result();
                        if ($categories && $categories->num_rows) {
                            while ($category = $categories->fetch_assoc()) {
                                echo '<span class="chip">' . htmlspecialchars($category['name']) . '</span>';
                            }
                        } else {
                            echo '<span class="muted">No categories</span>';
                        }
                        ?>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <h3>Specs</h3>
                    </div>
                    <ul class="list">
                        <?php
                        $specifications_stmt->execute();
                        $specifications = $specifications_stmt->get_result();
                        if ($specifications && $specifications->num_rows) {
                            while ($spec = $specifications->fetch_assoc()) {
                                echo '<li><strong>' . htmlspecialchars($spec['spec_name']) . ':</strong> ' . htmlspecialchars($spec['spec_value']) . '</li>';
                            }
                        } else {
                            echo '<li class="muted">No specs</li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <h3>Connectivity</h3>
                    </div>
                    <ul class="list">
                        <?php
                        $connectivity_stmt->execute();
                        $connectivity = $connectivity_stmt->get_result();
                        if ($connectivity && $connectivity->num_rows) {
                            while ($connect = $connectivity->fetch_assoc()) {
                                echo '<li>' . htmlspecialchars($connect['connectivity_type']) . ': ' . htmlspecialchars($connect['details']) . '</li>';
                            }
                        } else {
                            echo '<li class="muted">No connectivity details</li>';
                        }
                        ?>
                    </ul>
                </div>
            </section>

            <section class="panels">
                <div class="panel">
                    <div class="panel-head">
                        <h3>Dimensions</h3>
                    </div>
                    <ul class="list">
                        <?php
                        $dimensions_stmt->execute();
                        $dimensions = $dimensions_stmt->get_result();
                        if ($dimensions && $dimensions->num_rows) {
                            while ($dimension = $dimensions->fetch_assoc()) {
                                echo '<li>' . htmlspecialchars($dimension['dimension_name']) . ': ' . htmlspecialchars($dimension['dimension_value']) . '</li>';
                            }
                        } else {
                            echo '<li class="muted">No dimensions</li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <h3>Storage Stocks</h3>
                    </div>
                    <ul class="list">
                        <?php
                        $stocks_stmt->execute();
                        $stocks = $stocks_stmt->get_result();
                        if ($stocks && $stocks->num_rows) {
                            while ($stock = $stocks->fetch_assoc()) {
                                echo '<li>' . htmlspecialchars($stock['storage']) . ' — <span class="pill">' . (int) $stock['quantity'] . ' left</span></li>';
                            }
                        } else {
                            echo '<li class="muted">No stock data</li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <h3>Warranties</h3>
                    </div>
                    <ul class="list">
                        <?php
                        $warranties_stmt->execute();
                        $warranties = $warranties_stmt->get_result();
                        if ($warranties && $warranties->num_rows) {
                            while ($warranty = $warranties->fetch_assoc()) {
                                echo '<li><strong>Warranty:</strong> ' . htmlspecialchars($warranty['warranty_period']) . '</li><li class="muted">' . htmlspecialchars($warranty['warranty_details']) . '</li>';
                            }
                        } else {
                            echo '<li class="muted">No warranties</li>';
                        }
                        ?>
                    </ul>
                </div>
            </section>
        </div>
    </div>
    <script src="../Assets/Js/sidebar.js"></script>
    <script src="../Assets/Js/admin.js?v=20251018"></script>
    <script>
        // swap main image on thumbnail click
        (function () {
            var main = document.getElementById('mainImage');
            var thumbs = document.querySelectorAll('.gallery-thumbs .thumb');
            thumbs.forEach(function (t) { t.addEventListener('click', function () { main.src = t.src; }); });
        })();
    </script>
</body>

</html>