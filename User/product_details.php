<?php
session_start();
include '../config.php';

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
    // Default to base price only
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
    <link rel="stylesheet" href="../Assets/CSS/home.css">
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

    /* Toast for Add to Cart */
    .toast {
        position: fixed;
        right: 16px;
        bottom: 16px;
        background: #111827;
        color: #fff;
        padding: 12px 16px;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .2);
        opacity: 0;
        transform: translateY(10px);
        pointer-events: none;
        transition: opacity .25s ease, transform .25s ease;
        z-index: 1001;
        display: flex;
        align-items: center;
        gap: 12px;
        max-width: 320px;
    }

    .toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .toast.success {
        background: #065f46;
    }

    .toast.error {
        background: #7f1d1d;
    }

    .toast .toast-action {
        margin-left: auto;
        background: rgba(255, 255, 255, .15);
        color: #fff;
        border: none;
        padding: 6px 10px;
        border-radius: 6px;
        cursor: pointer;
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

    <!-- Add to Cart toast -->
    <div id="cartToast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

    <!------------------------->



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
            <div class="product-details">
                <div class="product-image" style="position:relative">
                    <img src="../upload/<?php echo htmlspecialchars($product['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>">
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
                                // For client-side dynamic update, data-price keeps base price; the AJAX endpoint will compute effective
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
                            <form id="add-to-cart-form" action="add_to_cart.php" method="POST"
                                style="display:inline-block;">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="hidden" name="storage_id" id="cart-storage-id"
                                    value="<?php echo htmlspecialchars($first_storage['storage_id']); ?>">
                                <input type="hidden" name="color" id="cart-color" value="">
                                <input type="hidden" name="quantity" id="cart-quantity" value="1">
                                <button type="submit" class="btn btn-primary" id="add-to-cart">Add to Cart</button>
                            </form>

                            <!-- Buy Now form -->
                            <form id="buy-now-form" action="buy_now.php" method="POST"
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
                    <form action="submit_review.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <!-- Automatically include the user ID from session -->
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
        // Tab switching for Additional Information with smooth height (user page)
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.ai-tab');
            const panels = document.querySelectorAll('.ai-panel');
            const container = document.querySelector('.ai-panels');

            function setContainerHeightToActive() {
                if (!container) return;
                const active = container.querySelector('.ai-panel.active');
                if (active) {
                    container.style.height = active.scrollHeight + 'px';
                }
            }
            setContainerHeightToActive();
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(setContainerHeightToActive, 150);
            });

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const target = tab.getAttribute('data-target');
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    tabs.forEach(t => t.setAttribute('aria-selected', t === tab ? 'true' : 'false'));
                    panels.forEach(p => p.classList.remove('active'));
                    const el = document.querySelector(target);
                    if (el) el.classList.add('active');
                    setContainerHeightToActive();
                });
            });
        });
    </script>
    <script>
        // Whether this product has color variants and requires a selection
        const requiresColor = <?php echo !empty($colors) ? 'true' : 'false'; ?>;

        document.querySelectorAll('input[name="storage"]').forEach(storageButton => {
            storageButton.addEventListener('change', async function () {
                let price = this.getAttribute('data-price');
                let quantity = parseInt(this.getAttribute('data-quantity'));
                const priceElement = document.getElementById('price');
                const maxQuantityElement = document.getElementById('max-quantity');
                const addToCartButton = document.getElementById('add-to-cart');
                const buyNowButton = document.getElementById('buy-now');
                const outOfStockMessage = document.querySelector('.out-of-stock');

                // Update price via endpoint (base price only)
                try {
                    const sid = this.value;
                    const resp = await fetch('get_storage_price.php?storage_id=' + encodeURIComponent(sid));
                    const text = await resp.text();
                    const eff = parseFloat(text.replace(/[^0-9.]/g, ''));
                    if (!isNaN(eff)) {
                        price = eff;
                    }
                } catch (e) { }
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
            storageButton.addEventListener('change', async function () {
                let price = this.getAttribute('data-price');
                let quantity = this.getAttribute('data-quantity');
                try {
                    const sid = this.value;
                    const resp = await fetch('get_storage_price.php?storage_id=' + encodeURIComponent(sid));
                    const text = await resp.text();
                    const eff = parseFloat(text.replace(/[^0-9.]/g, ''));
                    if (!isNaN(eff)) {
                        price = eff;
                    }
                } catch (e) { }

                // Update price and quantity display
                document.getElementById('price').textContent = 'Price: Rs ' + parseFloat(price).toFixed(2);
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


        // JavaScript to handle "Add to Cart" via AJAX with toast popup (no navigation)
        function showCartToast(message, type = 'success', actionLabel = 'View cart', actionHref = 'cart.php') {
            const toast = document.getElementById('cartToast');
            if (!toast) return;
            toast.className = 'toast ' + (type === 'error' ? 'error' : 'success');
            toast.innerHTML = `<span>${message}</span>` + (type !== 'error' ? ` <button class="toast-action" onclick="window.location.href='${actionHref}'">${actionLabel}</button>` : '');
            requestAnimationFrame(() => toast.classList.add('show'));
            clearTimeout(window.__cartToastTimer);
            window.__cartToastTimer = setTimeout(() => toast.classList.remove('show'), 2200);
        }

        async function doAddToCart(addBtn) {
            addBtn && (addBtn.disabled = true);
            try {
                // gather values
                const storageRadio = document.querySelector('input[name="storage"]:checked');
                const storageId = storageRadio?.value || document.getElementById('cart-storage-id').value;
                const quantityInput = document.getElementById('quantity');
                const quantity = parseInt(quantityInput.value, 10) || 1;
                const color = document.querySelector('.color-button.checked')?.getAttribute('data-color-name') || '';

                if (requiresColor && !color) {
                    showCartToast('Please select a color', 'error');
                    return;
                }

                const fd = new FormData();
                fd.append('product_id', '<?php echo $product_id; ?>');
                fd.append('storage_id', storageId);
                fd.append('color', color);
                fd.append('quantity', String(quantity));

                const res = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: fd
                });

                if (res.status === 401) {
                    // Not logged in: redirect to login and return here
                    window.location.href = `../login.php?redirect=User/product_details.php?id=<?php echo $product_id; ?>`;
                    return;
                }

                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) {
                    const msg = data && data.message ? data.message : 'Failed to add to cart';
                    showCartToast(msg, 'error');
                    // If server provided available quantity, adjust UI
                    if (typeof data.available !== 'undefined') {
                        const available = parseInt(data.available, 10) || 0;
                        document.getElementById('max-quantity').textContent = available > 0 ? ('Max Quantity: ' + available) : '';
                        quantityInput.max = available > 0 ? available : 1;
                    }
                    return;
                }

                // Success: show toast and update remaining stock UI
                showCartToast('Added to cart');
                if (typeof data.remaining !== 'undefined') {
                    const remaining = parseInt(data.remaining, 10);
                    document.getElementById('max-quantity').textContent = remaining > 0 ? ('Max Quantity: ' + remaining) : '';
                    quantityInput.max = remaining > 0 ? remaining : 1;
                    // Update the selected storage radio's data-quantity
                    if (storageRadio) {
                        const currentQty = parseInt(storageRadio.getAttribute('data-quantity') || '0', 10);
                        const newQty = Math.max(0, currentQty - quantity);
                        storageRadio.setAttribute('data-quantity', String(newQty));
                    }
                    const addToCartButton = document.getElementById('add-to-cart');
                    const buyNowButton = document.getElementById('buy-now');
                    const outOfStockMessage = document.querySelector('.out-of-stock');
                    if (remaining > 0) {
                        outOfStockMessage && (outOfStockMessage.style.display = 'none');
                        addToCartButton.classList.remove('disabled');
                        buyNowButton.classList.remove('disabled');
                        addToCartButton.style.pointerEvents = 'auto';
                        buyNowButton.style.pointerEvents = 'auto';
                    } else {
                        outOfStockMessage && (outOfStockMessage.style.display = 'block');
                        addToCartButton.classList.add('disabled');
                        buyNowButton.classList.add('disabled');
                        addToCartButton.style.pointerEvents = 'none';
                        buyNowButton.style.pointerEvents = 'none';
                    }
                }
            } catch (e) {
                showCartToast('Network error. Please try again.', 'error');
            } finally {
                addBtn && (addBtn.disabled = false);
            }
        }

        // Intercept button click
        document.getElementById('add-to-cart').addEventListener('click', function (event) {
            event.preventDefault();
            doAddToCart(this);
        });

        // Intercept form submit (e.g., Enter key)
        document.getElementById('add-to-cart-form').addEventListener('submit', function (event) {
            event.preventDefault();
            const btn = document.getElementById('add-to-cart');
            doAddToCart(btn);
        });

        document.getElementById('buy-now').addEventListener('click', function (event) {
            event.preventDefault();
            let storageId = document.querySelector('input[name="storage"]:checked')?.value || document.getElementById('buy-storage-id').value;
            let quantity = document.getElementById('quantity').value;
            let color = document.querySelector('.color-button.checked')?.getAttribute('data-color-name') || '';

            if (requiresColor && !color) {
                showCartToast('Please select a color', 'error');
                return;
            }

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
        // Toggle the review form visibility with smooth easing
        document.getElementById('writeReviewBtn').addEventListener('click', function () {
            const reviewForm = document.getElementById('reviewForm');
            reviewForm.classList.add('show'); // Add the class that shows the form
            setTimeout(() => {
                reviewForm.style.display = 'block'; // Ensure display block after the class is added
            }, 10); // Slight delay to allow transition
            document.getElementById('writeReviewBtn').style.display = 'none'; // Hide the "Write a review" button
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

        // Handle modal display for success or error messages
        const successMessage = "<?php echo $success_message; ?>";
        const errorMessage = "<?php echo $error_message; ?>";

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





</body>

</html>

<?php
$stmt->close();
$conn->close();
?>