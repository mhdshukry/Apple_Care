<?php
session_start();
include '../config.php';
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$user_id = $_SESSION['user_id'];
// Set current page to 'products' for this page
$current_page = 'products';
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
                <li class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">
                    <a href="./home.php">
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
                <li>
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
            <?php
            // Define the category titles to be displayed
            $category_titles = [
                1 => 'iPhone Products',
                2 => 'MacBook Products',
                3 => 'iPad Products',
                4 => 'Apple Watch Products',
                5 => 'Apple TV Products',
                6 => 'AirPods Products',
                7 => 'Accessories Products'
            ];

            foreach ($category_titles as $category_id => $title) {
                echo "<h1>$title</h1>";
                echo "<div class='products-container'>";

                $sql = "SELECT p.* FROM products p
                        JOIN product_categories pc ON p.product_id = pc.product_id
                        WHERE pc.category_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result === false) {
                    echo "Error: " . $conn->error;
                } elseif ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='product'>";
                        echo "<a href='product_details.php?id=" . $row['product_id'] . "'>";
                        echo "<img src='../upload/" . $row['image_url'] . "' alt='" . $row['name'] . "'>";
                        echo "<h3>" . $row['name'] . "</h3>";
                        echo "</a>";
                        echo "</div>";
                    }
                } else {
                    echo "No products found.";
                }

                echo "</div>";
            }

            $conn->close();
            ?>
        </div>
    </div>

    <script src="../Assets/Js/sidebar.js"></script>
</body>

</html>
