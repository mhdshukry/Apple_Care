<?php
session_start();
include 'config.php';

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

<!------------         Review     ------------------------>
<?php

// Handling success and error messages after submitting a review
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['success']);
unset($_SESSION['error']);

// Ensure a product ID is provided
if (!isset($_GET['id'])) {
    echo "Product ID is not set.";
    exit;
}

$product_id = intval($_GET['id']);

// Fetch product details from the database (assume you have this logic already)
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// Fetch reviews for the product
$review_sql = "
    SELECT reviews.rating, reviews.comment, reviews.review_date, customers.first_name, customers.last_name
    FROM reviews
    JOIN customers ON reviews.customer_id = customers.customer_id
    WHERE reviews.product_id = ?
    ORDER BY reviews.review_date DESC
";
$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param("i", $product_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Apple Care+</title>
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

<style>
    #reviewModal {
        display: none;
        /* Hide the modal by default */
        position: fixed;
        z-index: 1000;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        background-color: #fff;
        border-radius: 5px;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }

    /* Modal content styles */
    .modal-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    #modalCloseBtn {
        background-color: red;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    #modalCloseBtn:hover {
        background-color: darkred;
    }
</style>


<body>


    <!--------------------------           review ----------->

    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle"></h2>
            <p id="modalMessage"></p>
            <button id="modalCloseBtn">Close</button>
        </div>
    </div>

    <!------------------------->



    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="./Assets/Images/apple.png" alt="Logo">
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
            <div class="product-details">
                <div class="product-image">
                    <?php $imgPath = preg_replace('/^\.\.\//', '', $product['image_url']); ?>
                    <img src="<?php echo htmlspecialchars($imgPath); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-info">
                    <h1>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>
                    <div class="storage-price">
                        <p id="price">Price: Rs
                            <?php echo number_format($default_price, 2); ?>
                        </p>
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
                        <div class="quantity-input">
                            <button class="quantity-btn" onclick="decreaseValue()">-</button>
                            <input type="number" id="quantity" name="quantity" min="1" value="1">
                            <button class="quantity-btn" onclick="increaseValue()">+</button>
                        </div>
                        <p id="max-quantity" style="margin-top:5px; margin-left:13px;">Max Quantity:
                            <?php echo $default_quantity; ?>
                        </p>
                    </div>


                    <div class=" product-actions">
                        <?php if ($default_quantity > 0): ?>
                        <!-- Add to Cart form -->
                            <form id="add-to-cart-form" action="User/add_to_cart.php" method="POST"
                                style="display:inline-block;">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="hidden" name="storage_id" id="cart-storage-id"
                                    value="<?php echo htmlspecialchars($first_storage['storage_id']); ?>">
                                <input type="hidden" name="color" id="cart-color" value="">
                                <input type="hidden" name="quantity" id="cart-quantity" value="1">
                                <button type="submit" class="btn btn-primary" id="add-to-cart">Add to Cart</button>
                            </form>

                            <!-- Buy Now form -->
                            <form id="buy-now-form" action="User/buy_now.php" method="POST"
                                style="display:inline-block; margin-left:10px;">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="hidden" name="storage_id" id="buy-storage-id"
                                    value="<?php echo htmlspecialchars($first_storage['storage_id']); ?>">
                                <input type="hidden" name="color" id="buy-color" value="">
                                <input type="hidden" name="quantity" id="buy-quantity" value="1">
                                <button type="submit" class="btn btn-success" id="buy-now">Buy Now</button>
                            </form>
                            <p class="out-of-stock" style="color:red; display: none; width:max-content; margin-top:8px;">Out
                                of Stock</p>
                        <?php else: ?>
                            <p class="out-of-stock" style="color:red;">Out of Stock</p>
                        <?php endif; ?>
                    </div>


                </div>
            </div>

            <div class="additional-info ai-section">
                <h2>Additional Information</h2>
                <div class="ai-tabs" role="tablist">
                    <button class="ai-tab active" role="tab" aria-selected="true"
                        data-target="#ai-specs">Specifications</button>
                    <button class="ai-tab" role="tab" aria-selected="false"
                        data-target="#ai-connectivity">Connectivity</button>
                    <button class="ai-tab" role="tab" aria-selected="false"
                        data-target="#ai-dimensions">Dimensions</button>
                    <button class="ai-tab" role="tab" aria-selected="false" data-target="#ai-weight">Weight</button>
                    <button class="ai-tab" role="tab" aria-selected="false" data-target="#ai-warranty">Warranty</button>
                </div>
                <div class="ai-panels">
                    <div class="ai-panel active" id="ai-specs" role="tabpanel">
                        <div class="spec-grid">
                            <?php if (!empty($specifications)) { ?>
                                <?php foreach ($specifications as $spec): ?>
                                    <div class="ai-card spec-item">
                                        <div class="spec-name"><?php echo htmlspecialchars($spec['spec_name']); ?></div>
                                        <div class="spec-value"><?php echo htmlspecialchars($spec['spec_value']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php } else { ?>
                                <p class="ai-empty">No specifications available.</p>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="ai-panel" id="ai-connectivity" role="tabpanel">
                        <div class="spec-grid">
                            <?php if (!empty($connectivities)) { ?>
                                <?php foreach ($connectivities as $connectivity): ?>
                                    <div class="ai-card">
                                        <span
                                            class="chip chip-dark"><?php echo htmlspecialchars($connectivity['connectivity_type']); ?></span>
                                        <div class="ai-desc"><?php echo htmlspecialchars($connectivity['details']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php } else { ?>
                                <p class="ai-empty">No connectivity information.</p>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="ai-panel" id="ai-dimensions" role="tabpanel">
                        <div class="spec-grid">
                            <?php
                            $hasDimensions = false;
                            while ($dimension = $dimension_result->fetch_assoc()) {
                                $hasDimensions = true;
                                echo '<div class="ai-card">'
                                    . '<div class="spec-name">' . htmlspecialchars($dimension['dimension_name']) . '</div>'
                                    . '<div class="spec-value">' . htmlspecialchars($dimension['dimension_value']) . '</div>'
                                    . '</div>';
                            }
                            if (!$hasDimensions) {
                                echo '<p class="ai-empty">No dimensions listed.</p>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="ai-panel" id="ai-weight" role="tabpanel">
                        <div class="spec-grid">
                            <?php
                            $hasWeight = false;
                            while ($weight = $weight_result->fetch_assoc()) {
                                $hasWeight = true;
                                echo '<div class="ai-card weight-card">'
                                    . '<div class="ai-icon">⚖️</div>'
                                    . '<div class="ai-desc">' . htmlspecialchars($weight['weight_value']) . '</div>'
                                    . '</div>';
                            }
                            if (!$hasWeight) {
                                echo '<p class="ai-empty">No weight information.</p>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="ai-panel" id="ai-warranty" role="tabpanel">
                        <div class="spec-grid">
                            <?php
                            $hasWarranty = false;
                            while ($warranty = $warranty_result->fetch_assoc()) {
                                $hasWarranty = true;
                                echo '<div class="ai-card">'
                                    . '<span class="chip chip-accent">' . htmlspecialchars($warranty['warranty_period']) . '</span>'
                                    . '<div class="ai-desc">' . htmlspecialchars($warranty['warranty_details']) . '</div>'
                                    . '</div>';
                            }
                            if (!$hasWarranty) {
                                echo '<p class="ai-empty">No warranty details.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>


            <div class="reviews-container">
                <div class="review-header">
                    <h2>Used this product before?</h2>
                    <button id="writeReviewBtn" class="write-review-btn">Write a review</button>
                </div>

                <!-- Review form to be toggled -->
                <div class="review-form" id="reviewForm">
                    <button class="close-btn" id="closeFormBtn">&times;</button>
                    <form action="User/submit_review.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <textarea name="comment" placeholder="Enter your review" required></textarea>

                        <!-- Star rating -->
                        <div class="rating">
                            <span class="star far" data-rating="1"><span id="number">1</span> &#9733;</span>
                            <span class="star far" data-rating="2"><span id="number">2</span> &#9733;</span>
                            <span class="star far" data-rating="3"><span id="number">3</span> &#9733;</span>
                            <span class="star far" data-rating="4"><span id="number">4</span> &#9733;</span>
                            <span class="star far" data-rating="5"><span id="number">5</span> &#9733;</span>
                        </div>
                        <input type="hidden" name="rating" id="rating" value="0" required>

                        <button type="submit" class="button">Submit Review</button>
                    </form>
                </div>

                <!-- Review grid list -->
                <div class="review-list-grid">
                    <?php
                    if ($review_result->num_rows > 0) {
                        while ($review = $review_result->fetch_assoc()) {
                            $rating = intval($review['rating']);
                            $customer_name = htmlspecialchars($review['first_name']) . " " . htmlspecialchars($review['last_name']);
                            echo "<div class='review-grid-item'>";
                            echo "<div class='review-meta'>";
                            echo "<span class='username'>" . $customer_name . "</span>";
                            echo "<div class='rating'>";
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo "<span class='star filled'>&#9733;</span>"; // Filled star
                                } else {
                                    echo "<span class='star'>&#9733;</span>"; // Empty star
                                }
                            }
                            echo "</div>";
                            echo "</div>";
                            echo "<p class='comment'>" . htmlspecialchars($review['comment']) . "</p>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No reviews yet. Be the first to write a review!</p>";
                    }
                    ?>
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
                        $sugImg = preg_replace('/^\.\.\//', '', $suggested_product['image_url']);
                        echo "<img src='" . htmlspecialchars($sugImg) . "' alt='" . htmlspecialchars($suggested_product['name']) . "'>";
                        echo "<h3>" . htmlspecialchars($suggested_product['name']) . "</h3>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Tab switching for Additional Information with smooth height
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.ai-tab');
            const panels = document.querySelectorAll('.ai-panel');
            const container = document.querySelector('.ai-panels');

            function setContainerHeightToActive() {
                if (!container) return;
                const active = container.querySelector('.ai-panel.active');
                if (active) {
                    // Set explicit height to allow transition
                    container.style.height = active.scrollHeight + 'px';
                }
            }
            // Initialize height on load
            setContainerHeightToActive();
            // Recalculate on resize
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(setContainerHeightToActive, 150);
            });

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const target = tab.getAttribute('data-target');
                    // update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    tabs.forEach(t => t.setAttribute('aria-selected', t === tab ? 'true' : 'false'));
                    // update panels
                    panels.forEach(p => p.classList.remove('active'));
                    const el = document.querySelector(target);
                    if (el) el.classList.add('active');
                    // adjust container height to new active content
                    setContainerHeightToActive();
                });
            });
        });
    </script>
    <script>
        document.querySelectorAll('input[name="storage"]').forEach(storageButton => {
            storageButton.addEventListener('change', function () {
                let price = this.getAttribute('data-price');
                let quantity = parseInt(this.getAttribute(
                    'data-quantity')); // Convert quantity to an integer
                const priceElement = document.getElementById('price');
                const maxQuantityElement = document.getElementById('max-quantity');
                const addToCartButton = document.getElementById('add-to-cart');
                const buyNowButton = document.getElementById('buy-now');
                const outOfStockMessage = document.querySelector('.out-of-stock');

                // Update price
                priceElement.textContent = 'Price: Rs ' + parseFloat(price).toFixed(2);

                // Handle max quantity and out of stock message
                if (quantity > 0) {
                    maxQuantityElement.textContent = 'Max Quantity: ' + quantity;
                    addToCartButton.classList.remove('disabled');
                    buyNowButton.classList.remove('disabled');
                    addToCartButton.style.pointerEvents = 'auto';
                    buyNowButton.style.pointerEvents = 'auto';
                    outOfStockMessage.style.display = 'none'; // Hide out of stock message
                } else {
                    maxQuantityElement.textContent = '';
                    addToCartButton.classList.add('disabled');
                    buyNowButton.classList.add('disabled');
                    addToCartButton.style.pointerEvents = 'none';
                    buyNowButton.style.pointerEvents = 'none';
                    outOfStockMessage.style.display = 'block'; // Show out of stock message
                }

                document.getElementById('quantity').max = quantity > 0 ? quantity :
                    1; // Set max to 1 if out of stock
            });
        });
    </script>

    <script>
        // JavaScript to update the price and quantity based on the selected storage option
        document.querySelectorAll('input[name="storage"]').forEach(storageButton => {
            storageButton.addEventListener('change', function () {
                let price = this.getAttribute('data-price');
                let quantity = this.getAttribute('data-quantity');

                // Update price and quantity display
                document.getElementById('price').textContent = 'Price: Rs ' + parseFloat(price).toFixed(
                    2);
                document.getElementById('max-quantity').textContent = 'Max Quantity: ' + quantity;
                document.getElementById('quantity').max = quantity;

                // Remove active class from all buttons
                document.querySelectorAll('.storage-button').forEach(button => {
                    button.classList.remove('active');
                });

                // Add active class to the selected button's parent label
                this.parentElement.classList.add('active');
            });
        });

        // Ensure the default checked button is marked as active on page load
        document.querySelector('input[name="storage"]:checked')?.parentElement.classList.add('active');


        // JavaScript to handle "Add to Cart" and "Buy Now" via forms (POST) with auth gating
        document.getElementById('add-to-cart').addEventListener('click', function (event) {
            event.preventDefault();
            if (!window.isLoggedIn) {
                window.location.href = 'login.php?redirect=' + encodeURIComponent('product_details.php?id=<?php echo $product_id; ?>');
                return;
            }
            // gather values
            let storageId = document.querySelector('input[name="storage"]:checked')?.value || document.getElementById('cart-storage-id').value;
            let quantity = document.getElementById('quantity').value;
            let color = document.querySelector('.color-button.checked')?.getAttribute('data-color-name') || '';

            // populate form fields
            document.getElementById('cart-storage-id').value = storageId;
            document.getElementById('cart-quantity').value = quantity;
            document.getElementById('cart-color').value = color;

            // submit form
            document.getElementById('add-to-cart-form').submit();
        });

        document.getElementById('buy-now').addEventListener('click', function (event) {
            event.preventDefault();
            if (!window.isLoggedIn) {
                window.location.href = 'login.php?redirect=' + encodeURIComponent('product_details.php?id=<?php echo $product_id; ?>');
                return;
            }
            let storageId = document.querySelector('input[name="storage"]:checked')?.value || document.getElementById('buy-storage-id').value;
            let quantity = document.getElementById('quantity').value;
            let color = document.querySelector('.color-button.checked')?.getAttribute('data-color-name') || '';

            document.getElementById('buy-storage-id').value = storageId;
            document.getElementById('buy-quantity').value = quantity;
            document.getElementById('buy-color').value = color;

            document.getElementById('buy-now-form').submit();
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
        // Toggle the review form visibility with auth gating
        document.getElementById('writeReviewBtn').addEventListener('click', function (e) {
            if (!window.isLoggedIn) {
                e.preventDefault();
                window.location.href = 'login.php?redirect=' + encodeURIComponent('product_details.php?id=<?php echo $product_id; ?>');
                return;
            }
            const reviewForm = document.getElementById('reviewForm');
            reviewForm.classList.add('show');
            setTimeout(() => {
                reviewForm.style.display = 'block';
            }, 10);
            document.getElementById('writeReviewBtn').style.display = 'none';
        });

        document.getElementById('closeFormBtn').addEventListener('click', function () {
            const reviewForm = document.getElementById('reviewForm');
            reviewForm.classList.remove('show'); // Remove the class to hide the form
            setTimeout(() => {
                reviewForm.style.display = 'none'; // Ensure display none after the transition
            }, 500); // Delay equal to the transition duration
            document.getElementById('writeReviewBtn').style.display = 'inline-block'; // Show the "Write a review" button
        });


        // Handle star rating
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function () {
                const rating = this.getAttribute('data-rating');
                document.getElementById('rating').value = rating;

                // Update star appearance
                document.querySelectorAll('.star').forEach(s => {
                    if (parseInt(s.getAttribute('data-rating')) <= rating) {
                        s.classList.add('checked');
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.add('far');
                        s.classList.remove('fas');
                        s.classList.remove('checked');
                    }
                });
            });
        });

        // Gate review submission behind auth
        const reviewFormEl = document.querySelector('#reviewForm form');
        if (reviewFormEl) {
            reviewFormEl.addEventListener('submit', function (e) {
                if (!window.isLoggedIn) {
                    e.preventDefault();
                    window.location.href = 'login.php?redirect=' + encodeURIComponent('product_details.php?id=<?php echo $product_id; ?>');
                }
            });
        }

        // Handle modal display for success or error messages
        const successMessage = <?php echo json_encode($success_message); ?>;
        const errorMessage = <?php echo json_encode($error_message); ?>;

        if (successMessage || errorMessage) {
            const modal = document.getElementById('reviewModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');

            if (successMessage) {
                modalTitle.textContent = "Success";
                modalMessage.textContent = successMessage;
            } else if (errorMessage) {
                modalTitle.textContent = "Error";
                modalMessage.textContent = errorMessage;
            }

            modal.style.display = 'block';

            // Auto close the modal after 2000ms
            setTimeout(function () {
                modal.style.display = 'none';
            }, 2000);
        }

        // Close modal button
        document.getElementById('modalCloseBtn').addEventListener('click', function () {
            document.getElementById('reviewModal').style.display = 'none';
        });
    </script>

    <script>
        function decreaseValue() {
            var value = parseInt(document.getElementById('quantity').value, 10);
            value = isNaN(value) ? 1 : value;
            if (value > 1) {
                value--; // Decrease value by 1, but don't go below 1
            }
            document.getElementById('quantity').value = value;
        }

        function increaseValue() {
            var value = parseInt(document.getElementById('quantity').value, 10);
            var maxQuantity = document.getElementById('quantity').max ? parseInt(document.getElementById('quantity')
                .max) :
                Infinity; // Get max, or set it to Infinity if max is not set
            value = isNaN(value) ? 1 : value;

            if (value < maxQuantity) {
                value++; // Increase value by 1, but don't exceed max quantity
            }

            document.getElementById('quantity').value = value; // Set the value back to the input field
        }
    </script>





    <?php include_once __DIR__ . '/login_modal_include.php'; ?>
    <script>
        window.isLoggedIn = <?php echo (isset($_SESSION['user_id']) && $_SESSION['user_id']) ? 'true' : 'false'; ?>;
        // Gate review form open for guests (defensive in case earlier listener didn't run yet)
        const writeBtn = document.getElementById('writeReviewBtn');
        if (writeBtn) {
            writeBtn.addEventListener('click', (e) => {
                if (!window.isLoggedIn) {
                    e.preventDefault();
                    window.location.href = 'login.php?redirect=' + encodeURIComponent('product_details.php?id=<?php echo $product_id; ?>');
                }
            }, { capture: true });
        }
    </script>
    <script src="Assets/Js/modal.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>