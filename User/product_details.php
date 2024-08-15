<?php
session_start();
include '../config.php';

if (!isset($_GET['id'])) {
    echo "Product ID is not set.";
    exit;
}

$current_page = 'products';

$product_id = intval($_GET['id']); // Ensure the ID is an integer

// Fetch product details from the database
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No product found.";
    exit;
}

$product = $result->fetch_assoc();

// Fetch color options for the product
$color_sql = "SELECT colors.color_name, colors.color_hex 
              FROM product_color 
              JOIN colors ON product_color.color_id = colors.color_id 
              WHERE product_color.product_id = ?";
$color_stmt = $conn->prepare($color_sql);
$color_stmt->bind_param("i", $product_id);
$color_stmt->execute();
$color_result = $color_stmt->get_result();
$colors = [];

// Fetch available storage options and stock for the product
$storage_sql = "SELECT storage_options.storage_id, storage_options.storage, storage_stock.quantity, storage_options.price 
                FROM storage_options 
                JOIN storage_stock ON storage_options.storage_id = storage_stock.storage_id 
                WHERE storage_options.product_id = ?";
$storage_stmt = $conn->prepare($storage_sql);
$storage_stmt->bind_param("i", $product_id);
$storage_stmt->execute();
$storage_result = $storage_stmt->get_result();

$first_storage = $storage_result->fetch_assoc(); // Fetch the first storage option

if ($first_storage) {
    $default_price = $first_storage['price'];
    $default_quantity = $first_storage['quantity'];
    $storage_result->data_seek(0); // Reset result pointer to display all storage options
} else {
    $default_price = 'N/A';
    $default_quantity = 0;
}

$colors = [];
while ($color = $color_result->fetch_assoc()) {
    $colors[] = $color;
}


$spec_sql = "SELECT * FROM product_specifications WHERE product_id = ?";
$spec_stmt = $conn->prepare($spec_sql);
$spec_stmt->bind_param("i", $product_id);
$spec_stmt->execute();
$spec_result = $spec_stmt->get_result();
$specifications = [];
while ($spec = $spec_result->fetch_assoc()) {
    $specifications[] = $spec;
}

// Fetch product connectivity
$connectivity_sql = "SELECT * FROM product_connectivity WHERE product_id = ?";
$connectivity_stmt = $conn->prepare($connectivity_sql);
$connectivity_stmt->bind_param("i", $product_id);
$connectivity_stmt->execute();
$connectivity_result = $connectivity_stmt->get_result();
$connectivities = [];
while ($connectivity = $connectivity_result->fetch_assoc()) {
    $connectivities[] = $connectivity;
}

$dimension_sql = "SELECT dimension_name, dimension_value FROM product_dimensions WHERE product_id = ?";
$dimension_stmt = $conn->prepare($dimension_sql);
$dimension_stmt->bind_param("i", $product_id);
$dimension_stmt->execute();
$dimension_result = $dimension_stmt->get_result();

$warranty_sql = "SELECT warranty_period, warranty_details FROM product_warranties WHERE product_id = ?";
$warranty_stmt = $conn->prepare($warranty_sql);
$warranty_stmt->bind_param("i", $product_id);
$warranty_stmt->execute();
$warranty_result = $warranty_stmt->get_result();

$weight_sql = "SELECT weight_value FROM product_weight WHERE product_id = ?";
$weight_stmt = $conn->prepare($weight_sql);
$weight_stmt->bind_param("i", $product_id);
$weight_stmt->execute();
$weight_result = $weight_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Apple Care+</title>
    <link rel="stylesheet" href="../Assets/CSS/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <!-- Sidebar content -->
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
                <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>"></li>
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
            <div class="product-details">
                <div class="product-image">
                    <img src="../upload/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="storage-price">
                            <p id="price">Price: Rs<?php echo number_format($default_price, 2); ?></p>
                        </div>
                    <div class="storage-options">
                        <h2>Select Storage</h2>
                        <div class="storage-buttons">
                            <?php
                            // Display all storage options
                            while ($storage = $storage_result->fetch_assoc()) {
                                $checked = ($storage['storage_id'] == $first_storage['storage_id']) ? "checked" : "";
                                echo "<label class='storage-button'>";
                                echo "<input type='radio' name='storage' value='" . $storage['storage_id'] . "' data-price='" . $storage['price'] . "' data-quantity='" . $storage['quantity'] . "' $checked>";
                                echo htmlspecialchars($storage['storage']);
                                echo "</label>";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="color-options">
                        <h2>Select Color</h2>
                        <div class="color-buttons">
                            <?php
                            foreach ($colors as $color) {
                                echo "<button class='color-button' style='background-color: " . htmlspecialchars($color['color_hex']) . ";' title='" . htmlspecialchars($color['color_name']) . "' data-color-name='" . htmlspecialchars($color['color_name']) . "'></button>";
                            }
                            ?>
                            <a href="#" class="color-clear" id="clear-color">
                                <span>X</span> Clear
                            </a>
                        </div>
                    </div>

                    <div class="quantity-selection">
                        <h2>Select Quantity</h2>
                        <input type="number" id="quantity" name="quantity" min="1" value="1">
                        <p id="max-quantity">Max Quantity: <?php echo $default_quantity; ?></p>
                    </div>

                    <div class="product-actions">
                        <?php if ($default_quantity > 0): ?>
                            <a href="add_to_cart.php?id=<?php echo $product_id; ?>&quantity=" id="add-to-cart" class="btn btn-primary">Add to Cart</a>
                            <a href="buy_now.php?id=<?php echo $product_id; ?>&quantity=" id="buy-now" class="btn btn-success">Buy Now</a>
                        <?php else: ?>
                            <p class="out-of-stock">Out of Stock</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

    <div class="additional-info">
    <h2>Additional Information</h2>
    <div class="info-table">
        <div class="info-row">
            <div class="info-header">Specification</div>
            <div class="info-header">Details</div>
        </div>
        <?php
        foreach ($specifications as $spec) {
            echo "<div class='info-row'>";
            echo "<div class='info-cell'><strong>" . htmlspecialchars($spec['spec_name']) . "</strong></div>";
            echo "<div class='info-cell'>" . htmlspecialchars($spec['spec_value']) . "</div>";
            echo "</div>";
        }
        ?>
        <?php
        foreach ($connectivities as $connectivity) {
            echo "<div class='info-row'>";
            echo "<div class='info-cell'><strong>" . htmlspecialchars($connectivity['connectivity_type']) . "</strong></div>";
            echo "<div class='info-cell'>" . htmlspecialchars($connectivity['details']) . "</div>";
            echo "</div>";
        }
        ?>
        <?php
        while ($dimension = $dimension_result->fetch_assoc()) {
            echo "<div class='info-row'>";
            echo "<div class='info-cell'><strong>" . htmlspecialchars($dimension['dimension_name']) . "</strong></div>";
            echo "<div class='info-cell'>" . htmlspecialchars($dimension['dimension_value']) . "</div>";
            echo "</div>";
        }
        ?>
        <?php
        while ($weight = $weight_result->fetch_assoc()) {
            echo "<div class='info-row'>";
            echo "<div class='info-cell'><strong>Weight</strong></div>";
            echo "<div class='info-cell'>" . htmlspecialchars($weight['weight_value']) . "</div>";
            echo "</div>";
        }
        ?>
        <?php
        while ($warranty = $warranty_result->fetch_assoc()) {
            echo "<div class='info-row'>";
            echo "<div class='info-cell'><strong>Warranty Period</strong></div>";
            echo "<div class='info-cell'>" . htmlspecialchars($warranty['warranty_period']) . "</div>";
            echo "</div>";
            echo "<div class='info-row'>";
            echo "<div class='info-cell'><strong>Details</strong></div>";
            echo "<div class='info-cell'>" . htmlspecialchars($warranty['warranty_details']) . "</div>";
            echo "</div>";
        }
        ?>
    </div>
</div>


            <div class="review-page">
                <div class="reviews-list">
                    <h2>Reviews</h2>
                    <?php
                    // Fetch reviews
                    $review_sql = "SELECT * FROM reviews WHERE product_id = ? ORDER BY review_date DESC";
                    $review_stmt = $conn->prepare($review_sql);
                    $review_stmt->bind_param("i", $product_id);
                    $review_stmt->execute();
                    $review_result = $review_stmt->get_result();

                    if ($review_result->num_rows > 0) {
                        while ($review = $review_result->fetch_assoc()) {
                            echo "<div class='review'>";
                            echo "</strong> " . htmlspecialchars($review['comment']) . "</p>";
                            echo "<p class='rating'>Rating: " . str_repeat("★", intval($review['rating'])) . "</p>";
                            echo "<p><em>Posted on: " . htmlspecialchars($review['review_date']) . "</em></p>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No reviews yet. Be the first to write a review!</p>";
                    }
                    ?>
                </div>
                <div class="review-form">
                    <h3>Write a Review</h3>
                    <form action="submit_review.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="rating">
                            <span class="star" data-rating="1">&#9733;</span>
                            <span class="star" data-rating="2">&#9733;</span>
                            <span class="star" data-rating="3">&#9733;</span>
                            <span class="star" data-rating="4">&#9733;</span>
                            <span class="star" data-rating="5">&#9733;</span>
                        </div>
                        <input type="hidden" name="rating" id="rating" value="0">
                        <textarea name="comment" placeholder="Enter your review" required></textarea>
                        <input type="text" name="username" placeholder="Your name" required>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>

            <div class="suggested-products">
                <h2>Suggested Products</h2>
                <div class="products-container">
                    <?php
                    $suggested_sql = "SELECT * FROM products WHERE product_id != ? LIMIT 5";
                    $suggested_stmt = $conn->prepare($suggested_sql);
                    $suggested_stmt->bind_param("i", $product_id);
                    $suggested_stmt->execute();
                    $suggested_result = $suggested_stmt->get_result();

                    while ($suggested_product = $suggested_result->fetch_assoc()) {
                        echo "<div class='product'>";
                        echo "<a href='product_details.php?id=" . htmlspecialchars($suggested_product['product_id']) . "'>";
                        echo "<img src='../upload/" . htmlspecialchars($suggested_product['image_url']) . "' alt='" . htmlspecialchars($suggested_product['name']) . "'>";
                        echo "<h3>" . htmlspecialchars($suggested_product['name']) . "</h3>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('input[name="storage"]').forEach(storageButton => {
    storageButton.addEventListener('change', function() {
        let price = this.getAttribute('data-price');
        let quantity = parseInt(this.getAttribute('data-quantity')); // Convert quantity to an integer
        const priceElement = document.getElementById('price');
        const maxQuantityElement = document.getElementById('max-quantity');
        const addToCartButton = document.getElementById('add-to-cart');
        const buyNowButton = document.getElementById('buy-now');

        if (quantity > 0) {
            priceElement.textContent = 'Price: Rs' + parseFloat(price).toFixed(2);
            maxQuantityElement.textContent = 'Max Quantity: ' + quantity;
            addToCartButton.classList.remove('disabled');
            buyNowButton.classList.remove('disabled');
            addToCartButton.style.pointerEvents = 'auto';
            buyNowButton.style.pointerEvents = 'auto';
        } else {
            priceElement.textContent = 'Out of Stock';
            maxQuantityElement.textContent = '';
            addToCartButton.classList.add('disabled');
            buyNowButton.classList.add('disabled');
            addToCartButton.style.pointerEvents = 'none';
            buyNowButton.style.pointerEvents = 'none';
        }

        document.getElementById('quantity').max = quantity > 0 ? quantity : 1; // Set max to 1 if out of stock
    });
});



    </script>

    <script>
        // JavaScript to update the price and quantity based on the selected storage option
        document.querySelectorAll('input[name="storage"]').forEach(storageButton => {
            storageButton.addEventListener('change', function() {
                let price = this.getAttribute('data-price');
                let quantity = this.getAttribute('data-quantity');
                document.getElementById('price').textContent = 'Price: Rs' + parseFloat(price).toFixed(2);
                document.getElementById('max-quantity').textContent = 'Max Quantity: ' + quantity;
                document.getElementById('quantity').max = quantity;
            });
        });

        // JavaScript to handle "Add to Cart" and "Buy Now" button click
        document.getElementById('add-to-cart').addEventListener('click', function(event) {
            event.preventDefault();
            let quantity = document.getElementById('quantity').value;
            this.href += quantity;
            window.location.href = this.href;
        });

        document.getElementById('buy-now').addEventListener('click', function(event) {
            event.preventDefault();
            let quantity = document.getElementById('quantity').value;
            this.href += quantity;
            window.location.href = this.href;
        });

        // Handle color selection
        document.addEventListener('DOMContentLoaded', () => {
            const colorButtons = document.querySelectorAll('.color-button');
            const clearColorButton = document.getElementById('clear-color');

            colorButtons.forEach(button => {
                button.addEventListener('click', () => {
                    colorButtons.forEach(btn => btn.classList.remove('checked'));
                    button.classList.add('checked');
                    const selectedColor = button.getAttribute('data-color-name');
                    console.log('Selected Color:', selectedColor);
                    // You can add code here to handle the color selection, like updating a hidden input or making an AJAX request
                });
            });

            clearColorButton.addEventListener('click', (e) => {
                e.preventDefault();
                colorButtons.forEach(btn => btn.classList.remove('checked'));
                console.log('Color selection cleared.');
                // You can add code here to handle clearing the selection, like resetting a hidden input or making an AJAX request
            });
        });
    </script>
    <script>
        starIcons.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-value');
                    ratingInput.value = rating;
                    starIcons.forEach(s => {
                        if (s.getAttribute('data-value') <= rating) {
                            s.classList.add('fas');
                            s.classList.remove('far');
                        } else {
                            s.classList.add('far');
                            s.classList.remove('fas');
                        }
                    });
                });
            });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
