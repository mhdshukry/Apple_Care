<?php
session_start();
include '../config.php';

$current_page = 'admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple_Care</title>
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
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
            <h1>Manage Products</h1>
            <a href="add_product.php" class="btn">Add New Product</a>
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
$sql = "SELECT p.product_id, p.name, p.model, p.image_url, s.storage, s.price, 
               COALESCE(SUM(ss.quantity), 0) AS stock
        FROM products p 
        LEFT JOIN storage_options s ON p.product_id = s.product_id
        LEFT JOIN storage_stock ss ON s.storage_id = ss.storage_id
        GROUP BY p.product_id, s.storage, s.price";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr onclick=\"window.location.href='product_details.php?id=" . $row['product_id'] . "'\">";
        echo "<td><img src='" . $row['image_url'] . "' alt='" . $row['name'] . "' width='50'></td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['model'] . "</td>";
        echo "<td>" . $row['stock'] . "</td>";
        echo "<td>" . $row['storage'] . "</td>";
        echo "<td>Rs" . $row['price'] . "</td>";
        echo "<td class='table-actions'>";
        echo "<a href='edit_product.php?id=" . $row['product_id'] . "' class='btn btn-secondary'>Edit</a> ";
        echo "<a href='delete_product.php?id=" . $row['product_id'] . "' class='btn btn-secondary'>Delete</a>";
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
    <script src="../Assets/Js/sidebar.js"></script>
</body>

</html>
