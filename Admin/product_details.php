<?php
session_start();
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
    <title>Product Details</title>
    <link rel="stylesheet" href="../Assets/CSS/pro_det.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap" rel="stylesheet">
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
                        <span>Manage Products</span>
                    </a>
                </li>
                <li>
                    <a href="manage_order.php">
                        <i class="fa fa-mobile"></i>
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
        <div class="main-content">
            <div class="product-details">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-images">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product Image" width="300">
                    <div class="additional-images">
                        <?php while ($image = $images->fetch_assoc()): ?>
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="Additional Image" width="150">
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="product-categories">
                    <h2>Categories</h2>
                    <ul>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($category['name']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="product-specifications">
                    <h2>Specifications</h2>
                    <ul>
                        <?php while ($spec = $specifications->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($spec['spec_name']) . ": " . htmlspecialchars($spec['spec_value']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="product-info">
                    <h2>Details</h2>
                    <p><strong>Model:</strong> <?php echo htmlspecialchars($product['model']); ?></p>
                    <p><strong>Battery Life:</strong> <?php echo htmlspecialchars($battery_life['battery_life']); ?></p>
                    <p><strong>Weight:</strong> <?php echo htmlspecialchars($weight['weight_value']); ?></p>
                </div>
                <div class="product-options">
                    <h2>Storage Options</h2>
                    <ul>
                        <?php while ($storage = $storage_options->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($storage['storage']) . " - Rs" . htmlspecialchars($storage['price']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="product-stocks">
                    <h2>Storage Stocks</h2>
                    <ul>
                        <?php while ($stock = $stocks->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($stock['storage']) . ": " . htmlspecialchars($stock['quantity']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="product-colors">
                    <h2>Colors</h2>
                    <ul>
                        <?php while ($color = $colors->fetch_assoc()): ?>
                            <li>
                                <span style="background-color: <?php echo htmlspecialchars($color['color_hex']); ?>; width: 20px; height: 20px; display: inline-block; border: 1px solid #000; margin-right: 10px;"></span>
                                <?php echo htmlspecialchars($color['color_name']); ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="product-connectivity">
                    <h2>Connectivity</h2>
                    <ul>
                        <?php while ($connect = $connectivity->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($connect['connectivity_type']); ?>: <?php echo htmlspecialchars($connect['details']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="product-dimensions">
                    <h2>Dimensions</h2>
                    <ul>
                        <?php while ($dimension = $dimensions->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($dimension['dimension_name']) . ": " . htmlspecialchars($dimension['dimension_value']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                
                <div class="product-warranties">
                    <h2>Warranties</h2>
                    <ul>
                        <?php while ($warranty = $warranties->fetch_assoc()): ?>
                            <li>Warranty:<?php echo htmlspecialchars($warranty['warranty_period']); ?></li>
                            <li>Warranty details:<?php echo htmlspecialchars($warranty['warranty_details']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="../Assets/Js/sidebar.js"></script>
</body>

</html>
