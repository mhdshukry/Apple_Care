<?php
session_start();
include 'config.php';

// Public page: no auth required
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

if ($category !== '') {
    // Filter by category when provided - use prepared statement
    $stmt = $conn->prepare("SELECT p.product_id, p.name, p.description, p.image_url 
        FROM products p
        INNER JOIN product_categories pc ON p.product_id = pc.product_id
        INNER JOIN categories c ON pc.category_id = c.category_id
        WHERE c.name = ?");
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Otherwise, show all products
    $result = $conn->query("SELECT product_id, name, description, image_url FROM products");
}

// Determine current page
$current_page = 'products';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+</title>
    <link rel="icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="shortcut icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="stylesheet" href="Assets/CSS/home.css">
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
                <img src="Assets/Images/apple.png" alt="Logo">
                <span>Apple Care+</span>
            </div>
            <ul>
                <li>
                    <a href="Index.php">
                        <i class="fa fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="active">
                    <a href="./products.php">
                        <i class="fa fa-mobile"></i>
                        <span>Products</span>
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
            <h1>
                <?php echo $category !== '' ? 'Products in Category: ' . htmlspecialchars($category) : 'All Products'; ?>
            </h1>
            <div class="products-container">
                <?php
                if (!$result) {
                    echo "<p>Error loading products.</p>";
                } else {
                    if (mysqli_num_rows($result) > 0) {
                        while ($product = $result->fetch_assoc()) {
                            $img = isset($product['image_url']) ? preg_replace('/^\.\.\//', '', $product['image_url']) : '';
                            // If the path doesn't start with 'upload/', prepend it
                            if ($img !== '' && strpos($img, 'upload/') !== 0 && strpos($img, 'Assets/') !== 0) {
                                $img = 'upload/' . ltrim($img, '/');
                            }
                            echo '<div class="product">';
                            echo '<a href="product_details.php?id=' . htmlspecialchars($product['product_id']) . '">';
                            echo '<img src="' . htmlspecialchars($img) . '" alt="' . htmlspecialchars($product['name']) . '">';
                            echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                            echo '</a>';
                            echo '</div>';
                        }
                    } else {
                        echo $category !== ''
                            ? "<p>No products found in this category.</p>"
                            : "<p>No products found.</p>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <script src="Assets/Js/sidebar.js"></script>
    <script>
        window.isLoggedIn = <?php echo (isset($_SESSION['user_id']) && $_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
</body>

</html>

<?php
// Close connection
mysqli_close($conn);
?>