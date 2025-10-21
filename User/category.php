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
            // Fetch categories dynamically to reflect current data
            $catSql = "SELECT category_id, name FROM categories ORDER BY name ASC";
            $catRes = $conn->query($catSql);

            if ($catRes && $catRes->num_rows > 0) {
                while ($cat = $catRes->fetch_assoc()) {
                    $category_id = (int) $cat['category_id'];
                    $category_name = $cat['name'];

                    echo '<h1>' . htmlspecialchars($category_name) . '</h1>';
                    echo "<div class='products-container'>";

                    $sql = "SELECT p.product_id, p.name, p.image_url
                            FROM products p
                            JOIN product_categories pc ON p.product_id = pc.product_id
                            WHERE pc.category_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $category_id);
                    $stmt->execute();

                    // Collect products with mysqlnd fallback
                    $products = [];
                    if (method_exists($stmt, 'get_result')) {
                        $res = $stmt->get_result();
                        if ($res !== false) {
                            while ($row = $res->fetch_assoc()) {
                                $products[] = $row;
                            }
                        } else {
                            $stmt->store_result();
                            $stmt->bind_result($pid, $pname, $pimg);
                            while ($stmt->fetch()) {
                                $products[] = [
                                    'product_id' => $pid,
                                    'name' => $pname,
                                    'image_url' => $pimg,
                                ];
                            }
                        }
                    } else {
                        $stmt->store_result();
                        $stmt->bind_result($pid, $pname, $pimg);
                        while ($stmt->fetch()) {
                            $products[] = [
                                'product_id' => $pid,
                                'name' => $pname,
                                'image_url' => $pimg,
                            ];
                        }
                    }
                    $stmt->close();

                    if (!empty($products)) {
                        foreach ($products as $p) {
                            echo "<div class='product'>";
                            echo "<a href='product_details.php?id=" . htmlspecialchars($p['product_id']) . "'>";
                            echo "<img src='../upload/" . htmlspecialchars($p['image_url']) . "' alt='" . htmlspecialchars($p['name']) . "'>";
                            echo "<h3>" . htmlspecialchars($p['name']) . "</h3>";
                            echo "</a>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No products found in this category.</p>";
                    }

                    echo "</div>"; // .products-container
                }
            } else {
                echo '<h1>Categories</h1><p>No categories available.</p>';
            }

            $conn->close();
            ?>
        </div>
    </div>

    <script src="../Assets/Js/sidebar.js"></script>
</body>

</html>