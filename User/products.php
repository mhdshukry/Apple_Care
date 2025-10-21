<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$user_id = $_SESSION['user_id'];

// Read category filter if provided
$category = isset($_GET['category']) ? trim($_GET['category']) : null;

// Build query using prepared statements; show all if category not provided
if ($category) {
    $stmt = $conn->prepare("SELECT p.product_id, p.name, p.description, p.image_url,
        (SELECT MIN(s.price) FROM storage_options s WHERE s.product_id = p.product_id) AS base_min_price
        FROM products p
        INNER JOIN product_categories pc ON p.product_id = pc.product_id
        INNER JOIN categories c ON pc.category_id = c.category_id
        WHERE c.name = ?");
    $stmt->bind_param('s', $category);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("SELECT p.product_id, p.name, p.description, p.image_url,
        (SELECT MIN(s.price) FROM storage_options s WHERE s.product_id = p.product_id) AS base_min_price
        FROM products p
        ORDER BY p.product_id DESC");
    $stmt->execute();
}

// Fetch rows, supporting environments without mysqlnd
$products = [];
if (method_exists($stmt, 'get_result')) {
    $tmp = $stmt->get_result();
    if ($tmp !== false) {
        while ($row = $tmp->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $stmt->store_result();
        $stmt->bind_result($pid, $pname, $pdesc, $pimg);
        while ($stmt->fetch()) {
            $products[] = [
                'product_id' => $pid,
                'name' => $pname,
                'description' => $pdesc,
                'image_url' => $pimg,
            ];
        }
    }
} else {
    $stmt->store_result();
    $stmt->bind_result($pid, $pname, $pdesc, $pimg);
    while ($stmt->fetch()) {
        $products[] = [
            'product_id' => $pid,
            'name' => $pname,
            'description' => $pdesc,
            'image_url' => $pimg,
        ];
    }
}

// Determine current page
$current_page = 'products'; // Set this based on the current page context
?>

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
                <li>
                    <a href="./home.php" class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">
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
                <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
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
            <h1><?php echo $category ? 'Products in Category: ' . htmlspecialchars($category) : 'All Products'; ?></h1>
            <div class="products-container">
                <?php
                if (!empty($products)) {
                    foreach ($products as $product) {
                        $base = isset($product['base_min_price']) ? (float) $product['base_min_price'] : 0.0;


                        echo '<div class="product">';
                        echo '<a href="product_details.php?id=' . htmlspecialchars($product['product_id']) . '">';
                        echo '<div style="position:relative">';
                        echo '<img src="../upload/' . htmlspecialchars($product['image_url']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                        echo '</div>';
                        echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                        echo '<p class="price">Rs.' . number_format($base, 2) . '</p>';
                        echo '</a>';
                        echo '</div>';
                    }
                } else {
                    echo $category ? "<p>No products found in this category.</p>" : "<p>No products available.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="../Assets/Js/sidebar.js"></script>
</body>

</html>

<?php
// Close connection
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
mysqli_close($conn);
?>