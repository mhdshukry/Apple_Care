<?php
include '../config.php';
$current_page = "admin";

$storage_options = []; // Initialize storage_options
$existing_image_url = ''; // Initialize existing_image_url

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $product = filter_input(INPUT_POST, 'product', FILTER_SANITIZE_STRING);
    $model = filter_input(INPUT_POST, 'model', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_SANITIZE_NUMBER_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $category_id = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_NUMBER_INT);
    $storage_options = filter_input(INPUT_POST, 'storage', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $upload_dir = '../upload/';
    $image_url = $_POST['existing_image_url'] ?? '';

    // Validate product_id
    if (empty($product_id)) {
        echo "Product ID is required.";
        exit();
    }

    // Handle image upload if a new file is selected
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image_url']['tmp_name'];
        $file_name = basename($_FILES['image_url']['name']);
        $image_url = $upload_dir . $file_name;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image_url']['type'], $allowed_types)) {
            echo "Only JPG, PNG, and GIF files are allowed.";
            exit();
        }

        if (!move_uploaded_file($file_tmp, $image_url)) {
            echo "Failed to upload image. Please check directory permissions.";
            exit();
        }
    }

    $conn->begin_transaction();

    try {
        // Update product details
        $stmt = $conn->prepare("UPDATE products SET name = ?, product = ?, model = ?, price = ?, stock = ?, description = ?, image_url = ? WHERE product_id = ?");
        $stmt->bind_param("ssssdssi", $name, $product, $model, $price, $stock, $description, $image_url, $product_id);
        $stmt->execute();
        $stmt->close();

        // Update category
        $stmt = $conn->prepare("UPDATE product_categories SET category_id = ? WHERE product_id = ?");
        $stmt->bind_param("ii", $category_id, $product_id);
        $stmt->execute();
        $stmt->close();

        // Fetch current storage options
        $current_storage_options = [];
        $stmt = $conn->prepare("SELECT storage_id, storage FROM storage_options WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $current_storage_options[$row['storage_id']] = $row['storage'];
        }
        $stmt->close();

        // Calculate storage options to be deleted
        $storage_ids_to_delete = [];
        foreach ($current_storage_options as $storage_id => $storage) {
            if (!in_array($storage, $storage_options)) {
                $storage_ids_to_delete[] = $storage_id;
            }
        }

        // Delete only if there are no references in the orders table
        if (!empty($storage_ids_to_delete)) {
            $in_clause = implode(',', array_fill(0, count($storage_ids_to_delete), '?'));
            $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE storage_id IN ($in_clause)");
            $stmt->bind_param(str_repeat('i', count($storage_ids_to_delete)), ...$storage_ids_to_delete);
            $stmt->execute();
            $stmt->bind_result($order_count);
            $stmt->fetch();
            $stmt->close();

            if ($order_count == 0) {
                $stmt = $conn->prepare("DELETE FROM storage_options WHERE storage_id IN ($in_clause)");
                $stmt->bind_param(str_repeat('i', count($storage_ids_to_delete)), ...$storage_ids_to_delete);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Cannot delete storage options as they are referenced by orders.";
                $conn->rollback();
                exit();
            }
        }

        // Insert new storage options
        $stmt = $conn->prepare("INSERT INTO storage_options (product_id, storage) VALUES (?, ?)");
        foreach ($storage_options as $storage) {
            $stmt->bind_param("is", $product_id, $storage);
            $stmt->execute();
        }
        $stmt->close();

        $conn->commit();

        header("Location: admin.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} elseif (isset($_GET['id'])) {
    $product_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($product_id)) {
        echo "Product ID is required.";
        exit();
    }

    $stmt = $conn->prepare("SELECT p.*, c.category_id FROM products p JOIN product_categories c ON p.product_id = c.product_id WHERE p.product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT * FROM categories");
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();

    if ($categories->num_rows == 0) {
        echo "No categories found.";
        exit();
    }

    $stmt = $conn->prepare("SELECT storage FROM storage_options WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $storage_options[] = $row['storage'];
    }
    $stmt->close();

    $existing_image_url = $product['image_url'];

    if (!$product) {
        echo "Product not found.";
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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
                <h1>Edit Product</h1>
                <form action="edit_product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id"
                        value="<?php echo htmlspecialchars($product['product_id']); ?>">

                    <label for="name">Product Name</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['name']); ?>"
                        required>

                    <label for="product">Product Type</label>
                    <input type="text" name="product" id="product"
                        value="<?php echo htmlspecialchars($product['product']); ?>" required>

                    <label for="model">Model</label>
                    <input type="text" name="model" id="model"
                        value="<?php echo htmlspecialchars($product['model']); ?>" required>

                    <label for="price">Price</label>
                    <input type="number" step="0.01" name="price" id="price"
                        value="<?php echo htmlspecialchars($product['price']); ?>" required>

                    <label for="stock">Stock</label>
                    <input type="number" name="stock" id="stock"
                        value="<?php echo htmlspecialchars($product['stock']); ?>" required>

                    <label for="description">Description</label>
                    <textarea name="description" id="description"
                        required><?php echo htmlspecialchars($product['description']); ?></textarea>

                    <div class="inline-container">
                        <div>
                            <label for="category">Category</label>
                            <select name="category" id="category" required>
                                <option value="">Select Category</option>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($cat['category_id']); ?>"
                                    <?php echo ($cat['category_id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label for="storage">Storage Options</label>
                            <select name="storage[]" id="storage" required>
                                <option value="64GB"
                                    <?php echo in_array('64GB', $storage_options) ? 'selected' : ''; ?>>64GB</option>
                                <option value="128GB"
                                    <?php echo in_array('128GB', $storage_options) ? 'selected' : ''; ?>>128GB</option>
                                <option value="256GB"
                                    <?php echo in_array('256GB', $storage_options) ? 'selected' : ''; ?>>256GB</option>
                                <option value="512GB"
                                    <?php echo in_array('512GB', $storage_options) ? 'selected' : ''; ?>>512GB</option>
                                <option value="1TB" <?php echo in_array('1TB', $storage_options) ? 'selected' : ''; ?>>
                                    1TB</option>
                            </select>
                        </div>

                        <div class="file-upload-wrapper">
                            <label class="file-upload-button">
                                <span class="file-upload-icon">📁</span>
                                <span class="file-upload-button-text">Choose File</span>
                                <input type="file" name="image_url" id="image_url">
                            </label>
                            <span class="file-upload-filename" id="file-upload-filename">
                                <?php if (!empty($existing_image_url)): ?>
                                <?php echo basename($existing_image_url); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn">Update Product</button>
                </form>
            </div>
        </div>
    </div>


    <script src="../Assets/Js/edit_product.js"></script>
    <script src="../Assets/Js/sidebar.js"></script>
</body>

</html>