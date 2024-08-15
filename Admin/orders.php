<?php
session_start();
include '../config.php';
$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple_Care - Orders</title>
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
                <li class="<?php echo ($current_page == 'manage_order') ? 'active' : ''; ?>">
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
            <h1>Orders</h1>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Product Name</th>
                        <th>Color</th>
                        <th>Storage</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
$sql = "
SELECT 
    o.order_id,
    c.first_name,
    c.last_name,
    o.order_date,
    o.total_amount,
    o.status,
    p.name AS product_name,
    o.item_color AS color,
    s.storage AS storage,  -- Updated to use the storage value from storage_options
    o.total_quantity
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
JOIN products p ON o.product_id = p.product_id
JOIN storage_options s ON o.storage_id = s.storage_id  -- Join storage_options to get the storage value
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['order_id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['order_date'] . "</td>";
        echo "<td>Rs" . $row['total_amount'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['product_name'] . "</td>"; 
        echo "<td>" . $row['color'] . "</td>"; 
        echo "<td>" . $row['storage'] . "</td>"; 
        echo "<td>" . $row['total_quantity'] . "</td>"; 
        echo "<td>";
        echo "<a href='delete_order.php?id=" . $row['order_id'] . "' class='btn'>Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10'>No orders found.</td></tr>";
}
?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="../Assets/Js/sidebar.js"></script>
</body>

</html>