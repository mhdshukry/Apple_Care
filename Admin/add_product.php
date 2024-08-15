<?php
include '../config.php';
$current_page = "admin";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $product = $_POST['product'];
    $model = $_POST['model'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    $category_id = $_POST['category'];
    $storage = $_POST['storage']; 
    $weight_value = $_POST['weight_value'];
    $warranty_period = $_POST['warranty_period'];
    $warranty_details = $_POST['warranty_details'];
    $spec_name = $_POST['spec_name'];
    $spec_value = $_POST['spec_value'];
    $dimension_name = $_POST['dimension_name'];
    $dimension_value = $_POST['dimension_value'];
    $connectivity_type = $_POST['connectivity_type'];
    $connectivity_details = $_POST['connectivity_details'];
    $color_id = $_POST['color'];
    $battery_life = $_POST['battery_life'];
    $upload_dir = '../upload/';
    $image_url = $upload_dir . basename($_FILES['image_url']['name']);

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES['image_url']['tmp_name'], $image_url)) {
        $conn->begin_transaction();
        try {
            // Insert product into the products table
            $sql = "INSERT INTO products (name, model, stock, description, image_url, price) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssissi", $name, $product, $model, $stock, $description, $image_url, $price);
            $stmt->execute();
            $product_id = $stmt->insert_id;
            $stmt->close();

            // Insert product-category relationship
            $sql_category = "INSERT INTO product_categories (product_id, category_id) 
                             VALUES (?, ?)";
            $stmt = $conn->prepare($sql_category);
            $stmt->bind_param("ii", $product_id, $category_id);
            $stmt->execute();
            $stmt->close();

            // Insert storage options
            $sql_storage = "INSERT INTO storage_options (product_id, storage) 
                            VALUES (?, ?)";
            $stmt = $conn->prepare($sql_storage);
            $stmt->bind_param("is", $product_id, $storage);
            $stmt->execute();
            $stmt->close();

            // Insert weight details
            $sql_weight = "INSERT INTO product_weight (product_id, weight_value) 
                           VALUES (?, ?)";
            $stmt = $conn->prepare($sql_weight);
            $stmt->bind_param("is", $product_id, $weight_value);
            $stmt->execute();
            $stmt->close();

            // Insert warranty details
            $sql_warranty = "INSERT INTO product_warranties (product_id, warranty_period, warranty_details) 
                             VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_warranty);
            $stmt->bind_param("iss", $product_id, $warranty_period, $warranty_details);
            $stmt->execute();
            $stmt->close();

            // Insert specifications
            $sql_spec = "INSERT INTO product_specifications (product_id, spec_name, spec_value) 
                         VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_spec);
            $stmt->bind_param("iss", $product_id, $spec_name, $spec_value);
            $stmt->execute();
            $stmt->close();

            // Insert dimensions
            $sql_dimension = "INSERT INTO product_dimensions (product_id, dimension_name, dimension_value) 
                              VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_dimension);
            $stmt->bind_param("iss", $product_id, $dimension_name, $dimension_value);
            $stmt->execute();
            $stmt->close();

            // Insert connectivity details
            $sql_connectivity = "INSERT INTO product_connectivity (product_id, connectivity_type, connectivity_details) 
                                 VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_connectivity);
            $stmt->bind_param("iss", $product_id, $connectivity_type, $connectivity_details);
            $stmt->execute();
            $stmt->close();

            // Insert color details
            $sql_color = "INSERT INTO product_colors (product_id, color_id) 
                          VALUES (?, ?)";
            $stmt = $conn->prepare($sql_color);
            $stmt->bind_param("ii", $product_id, $color_id);
            $stmt->execute();
            $stmt->close();

            // Insert battery life details
            $sql_battery = "INSERT INTO product_battery_life (product_id, battery_life) 
                            VALUES (?, ?)";
            $stmt = $conn->prepare($sql_battery);
            $stmt->bind_param("is", $product_id, $battery_life);
            $stmt->execute();
            $stmt->close();

            // Insert product image
            $sql_image = "INSERT INTO product_images (product_id, image_url) 
                          VALUES (?, ?)";
            $stmt = $conn->prepare($sql_image);
            $stmt->bind_param("is", $product_id, $image_url);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            header("Location: admin.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Failed to upload image. Please check directory permissions.";
    }
}
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
                    <li><a href="#" class="social-media-link"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#" class="social-media-link"><i class="fab fa-facebook"></i></a></li>
                    <li><a href="#" class="social-media-link"><i class="fab fa-linkedin"></i></a></li>
                    <li><a href="#" class="social-media-link"><i class="fab fa-instagram"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="main-content">
            <div class="form-container">
                <h1>Add Product</h1>
                <form action="add_product.php" method="POST" enctype="multipart/form-data">
                    <label for="name">Product Name</label>
                    <input type="text" name="name" id="name" required>

                    <label for="model">Model</label>
                    <input type="text" name="model" id="model" required>

                    <label for="price">Price</label>
                    <input type="number" step="0.01" name="price" id="price" required>

                    <label for="stock">Stock</label>
                    <input type="number" name="stock" id="stock" required>

                    <label for="description">Description</label>
                    <textarea name="description" id="description" required></textarea>

                    <label for="category">Category</label>
                    <select name="category" id="category" required>
                        <option value="1">Iphone</option>
                        <option value="2">MacBook</option>
                        <option value="3">Wearables</option>
                        <option value="4">Accessories</option>
                    </select>

                    <label for="storage">Storage Options</label>
                    <select name="storage" id="storage" required>
                        <option value="64GB">64GB</option>
                        <option value="128GB">128GB</option>
                        <option value="256GB">256GB</option>
                        <option value="512GB">512GB</option>
                        <option value="1TB">1TB</option>
                    </select>

                    <label for="weight_value">Weight</label>
                    <input type="text" name="weight_value" id="weight_value" required>

                    <label for="warranty_period">Warranty Period</label>
                    <input type="text" name="warranty_period" id="warranty_period" required>

                    <label for="warranty_details">Warranty Details</label>
                    <textarea name="warranty_details" id="warranty_details"></textarea>

                    <label for="spec_name">Specification Name</label>
                    <input type="text" name="spec_name" id="spec_name" required>

                    <label for="spec_value">Specification Value</label>
                    <input type="text" name="spec_value" id="spec_value" required>

                    <label for="dimension_name">Dimension Name</label>
                    <input type="text" name="dimension_name" id="dimension_name" required>

                    <label for="dimension_value">Dimension Value</label>
                    <input type="text" name="dimension_value" id="dimension_value" required>

                    <label for="connectivity_type">Connectivity Type</label>
                    <input type="text" name="connectivity_type" id="connectivity_type" required>

                    <label for="connectivity_details">Connectivity Details</label>
                    <textarea name="connectivity_details" id="connectivity_details"></textarea>

                    <label for="color">Color</label>
                    <select name="color" id="color" required>
                        <?php
                        $sql_colors = "SELECT * FROM colors";
                        $result_colors = $conn->query($sql_colors);
                        while ($row = $result_colors->fetch_assoc()) {
                            echo "<option value='" . $row['color_id'] . "'>" . $row['color_name'] . "</option>";
                        }
                        ?>
                    </select>

                    <label for="battery_life">Battery Life</label>
                    <input type="text" name="battery_life" id="battery_life" required>

                    <label class="file-upload-button">
                                <span class="file-upload-icon">📁</span>
                                <span class="file-upload-button-text">Choose File</span>
                                <input type="file" name="image_url" id="image_url" required>
                            </label>
                            <span class="file-upload-filename" id="file-upload-filename"></span>

                    <button type="submit" class="btn">Add Product</button>
                </form>
            </div>
        </div>
    </div>

    <script src="../Assets/Js/sidebar.js"></script>
</body>

</html>
