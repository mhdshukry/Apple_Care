<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple_Care</title>
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
        <li class="active">
            <a href="./home.php">
                <i class="fa fa-home"></i>
                <span>Home</span>
            </a>
        </li>
        <li>
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
            <div class="slider">
                <div class="slide active">
                    <div class="content">
                        <h1>For Everything and Everyone</h1>
                        <p>Even if you're less into design and more into content strategy, you may find some redeeming value with, wait for it, dummy copy.</p>
                        <div class="buttons">
                            <a href="#" class="btn btn-primary">TO SHOP</a>
                            <a href="#" class="btn btn-secondary">READ MORE</a>
                        </div>
                    </div>
                    <img src="../Assets/Images/accessories-banner-1.jpg" alt="Slide Image">
                </div>
                <div class="slide">
                    <div class="content">
                        <h1>Featured <br> Accessories</h1>
                        <p>A client that's unhappy for a reason is a problem, a client that's unhappy though required he or her can't quite put a finger</p>
                        <div class="buttons">
                            <a href="#" class="btn btn-primary">TO SHOP</a>
                            <a href="#" class="btn btn-secondary">READ MORE</a>
                        </div>
                    </div>
                    <img src="../Assets/Images/accessories-banner-2.jpg" alt="Slide Image">
                </div>
                <div class="slide">
                    <div class="content">
                        <h1>Charge Your Phone<br> Safely!</h1>
                        <p>A wonderful serenity has taken possession of my entire soul, like these sweet mornings of spring which I enjoy with my whole heart</p>
                        <div class="buttons">
                            <a href="#" class="btn btn-primary">TO SHOP</a>
                            <a href="#" class="btn btn-secondary">READ MORE</a>
                        </div>
                    </div>
                    <img src="../Assets/Images/accessories-banner-3.jpg" alt="Slide Image">
                </div>
            </div>
        <div class="product-categories">
        <div class="category-box">
        <a href="products.php?category=iphone">
            <img src="../Assets/Images/iphone.jpeg" alt="Cases">
            <h3>Iphone</h3>
            <p>99+ products</p>
            </a>
        </div>
        <div class="category-box">
        <a href="products.php?category=MacBook">
            <img src="../Assets/Images/mac.jpg" alt="MagSafe">
            <h3>MacBook</h3>
            <p>30+ products</p>
            </a>
        </div>
        <div class="category-box">
        <a href="products.php?category=Ipad">
            <img src="../Assets/Images/ipad.png" alt="MagSafe">
            <h3>Ipad</h3>
            <p>30+ products</p>
            </a>
        </div>
        <div class="category-box">
        <a href="products.php?category=Watches">
            <img src="../Assets/Images/accessories-product-olive-strap-1-430x491.jpg" alt="Cables">
            <h3>Watches</h3>
            <p>20+ products</p>
            </a>
        </div>
        <div class="category-box">
        <a href="products.php?category=TV">
            <img src="../Assets/Images/4k.png" alt="Cables">
            <h3>Apple TV</h3>
            <p>20+ products</p>
            </a>
        </div>
        <div class="category-box">
        <a href="products.php?category=AirPods">
            <img src="../Assets/Images/airpods.png" alt="Cables">
            <h3>AirPods</h3>
            <p>20+ products</p>
            </a>
        </div>
        <div class="category-box">
        <a href="products.php?category=Accessories">
            <img src="../Assets/Images/HPGS2.jpeg" alt="Charger">
            <h3>Accessories</h3>
            <p>20+ products</p>
            </a>
        </div>
    </div>

    <section class="about-us">
        <div class="about-text">
            <p class="subtitle">Some words about us</p>
            <h1>We Help Everyone Enjoy Amazing Products</h1>
            <p class="description">If the copy becomes distracting in the design then you are doing something wrong or they are discussing copy changes. It might be a bit annoying but you could tell them that that discussion would be best suited.</p>
        </div>
        <div class="about-images">
            <div class="image-box">
                <img src="../Assets/Images/a.jpg" alt="Team">
            </div>
            <div class="testimonial-box">
                <p>Websites in professional use templating systems. Commercial publishing platforms and content management systems ensure that you can show.</p>
                <div class="testimonial-author">
                    <img src="../Assets/Images/accessories-inst-6.jpg" alt="Author">
                    <div>
                        <h4>Fathima Athiyya</h4>
                        <p>SKR pvt ltd.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- New Delivery, Best Quality, Free Return Section -->
    <section class="services">
        <div class="service-box">
            <i class="fas fa-shipping-fast"></i>
            <h3>Delivery</h3>
            <p>Get your order delivered to your doorstep promptly and safely.</p>
        </div>
        <div class="service-box">
            <i class="fas fa-check-circle"></i>
            <h3>Best Quality</h3>
            <p>We ensure the highest quality for all our products and services.</p>
        </div>
        <div class="service-box">
            <i class="fas fa-undo-alt"></i>
            <h3>Free Return</h3>
            <p>Enjoy hassle-free returns within 30 days of purchase.</p>
        </div>
    </section>

    </div>
    </div>


    <script src="../Assets/Js/sidebar.js"></script>
    <script src="../Assets/Js/slider.js"></script>
</body>

</html>
