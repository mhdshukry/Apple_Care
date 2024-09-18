<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$user_id = $_SESSION['user_id'];

$category = mysqli_real_escape_string($conn, $_GET['category']); // Sanitize input

// Join products with categories to fetch relevant products
$query = "
    SELECT p.name, p.description, p.image_url 
    FROM products p
    INNER JOIN product_categories pc ON p.product_id = pc.product_id
    INNER JOIN categories c ON pc.category_id = c.category_id
    WHERE c.name = '$category'
";
$result = mysqli_query($conn, $query);

// Determine current page
$current_page = 'products'; // Set this based on the current page context
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+</title>
    <link rel="stylesheet" href="../Assets/CSS/home.css">
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
                <li>
                    <a href="./home.php" class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">
                        <i class="fa fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="category.php" class="<?php echo ($current_page == 'products') ? 'active' : ''; ?>">
                        <i class="fa fa-mobile"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="#search-job">
                        <i class="fas fa-cart-plus"></i>
                        <span>Add to Cart</span>
                    </a>
                </li>
                <li>
                    <a href="#applications">
                        <i class="fa fa-user"></i>
                        <span>About Us</span>
                    </a>
                </li>
                <li>
                    <a href="#message">
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
            <h1>Products in Category: <?php echo htmlspecialchars($category); ?></h1>
            <div class="products-container">
                <?php
                if (!$result) {
                    // If query fails, display the error
                    echo "<p>Error: " . mysqli_error($conn) . "</p>";
                } else {
                    // Check if there are products
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<div class='product'>";
                            echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='Product Image' >";
                            echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No products found in this category.</p>";
                    }
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
mysqli_close($conn);
?>
