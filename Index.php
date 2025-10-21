<?php
// Load some featured products for the homepage
session_start();
include 'config.php';
$prodSql = "
        SELECT 
            p.product_id,
            p.name,
            p.image_url,
            MIN(s.price) AS base_min_price
        FROM products p
        JOIN storage_options s ON p.product_id = s.product_id
        GROUP BY p.product_id
        ORDER BY p.product_id DESC
        LIMIT 8
";
$prodRes = $conn->query($prodSql);
$products = $prodRes ? $prodRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+</title>
    <link rel="stylesheet" href="./Assets/CSS/style.css">
    <link rel="stylesheet" href="./Assets/CSS/hero.css">
    <link rel="stylesheet" href="./Assets/CSS/auth.css">
    <link rel="icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
    <link rel="shortcut icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png">
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
                <img src="./Assets/Images/apple.png" alt="Logo">
                <span>Apple Care+</span>
            </div>
            <ul>
                <li class="active">
                    <a href="Index.php">
                        <i class="fa fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
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

        <header class="header">
            <div class="news">Already have an account?</div>
            <div class="auth-buttons">
                <button class="btn" onclick="openModal('loginModal')">Sign In</button>
                <button class="btn" onclick="openModal('signupModal')">Sign Up</button>
            </div>
        </header>

        <div class="main-content">
            <div class="slider">
                <div class="slide active">
                    <div class="content">
                        <span class="eyebrow">New Season</span>
                        <h1>For Everything and Everyone</h1>
                        <p>Even if you're less into design and more into content strategy, you may find some redeeming
                            value with, wait for it, dummy copy.</p>
                        <div class="buttons">
                            <a href="products.php" class="btn btn-primary">TO SHOP</a>
                            <a href="#about" class="btn btn-secondary">READ MORE</a>
                        </div>
                    </div>
                    <img src="./Assets/Images/accessories-banner-1.png" alt="Slide Image">
                </div>
                <div class="slide">
                    <div class="content">
                        <span class="eyebrow">Top Picks</span>
                        <h1>Featured <br> Accessories</h1>
                        <p>A client that's unhappy for a reason is a problem, a client that's unhappy though required he
                            or her can't quite put a finger</p>
                        <div class="buttons">
                            <a href="products.php" class="btn btn-primary">TO SHOP</a>
                            <a href="#about" class="btn btn-secondary">READ MORE</a>
                        </div>
                    </div>
                    <img src="./Assets/Images/accessories-banner-2.png" alt="Slide Image">
                </div>
                <div class="slide">
                    <div class="content">
                        <span class="eyebrow">Power Up</span>
                        <h1>Charge Your Phone<br> Safely!</h1>
                        <p>A wonderful serenity has taken possession of my entire soul, like these sweet mornings of
                            spring which I enjoy with my whole heart</p>
                        <div class="buttons">
                            <a href="products.php" class="btn btn-primary">TO SHOP</a>
                            <a href="#about" class="btn btn-secondary">READ MORE</a>
                        </div>
                    </div>
                    <img src="./Assets/Images/accessories-banner-3.png" alt="Slide Image">
                </div>
            </div>
            <div class="product-categories">
                <div class="category-box">
                    <a href="products.php?category=iphone">
                        <img src="./Assets/Images/iphone.jpeg" alt="iPhone">
                        <h3>iPhone</h3>
                        <p>99+ products</p>
                    </a>
                </div>
                <div class="category-box">
                    <a href="products.php?category=MacBook">
                        <img src="./Assets/Images/mac.jpg" alt="MacBook">
                        <h3>MacBook</h3>
                        <p>30+ products</p>
                    </a>
                </div>
                <div class="category-box">
                    <a href="products.php?category=Ipad">
                        <img src="./Assets/Images/ipad.png" alt="iPad">
                        <h3>iPad</h3>
                        <p>30+ products</p>
                    </a>
                </div>
                <div class="category-box">
                    <a href="products.php?category=Watches">
                        <img src="./Assets/Images/accessories-product-olive-strap-1-430x491.jpg" alt="Watches">
                        <h3>Watches</h3>
                        <p>20+ products</p>
                    </a>
                </div>
                <div class="category-box">
                    <a href="products.php?category=TV">
                        <img src="./Assets/Images/4k.png" alt="Apple TV">
                        <h3>Apple TV</h3>
                        <p>20+ products</p>
                    </a>
                </div>
                <div class="category-box">
                    <a href="products.php?category=AirPods">
                        <img src="./Assets/Images/airpods.png" alt="AirPods">
                        <h3>AirPods</h3>
                        <p>20+ products</p>
                    </a>
                </div>
                <div class="category-box">
                    <a href="products.php?category=Accessories">
                        <img src="./Assets/Images/HPGS2.jpeg" alt="Accessories">
                        <h3>Accessories</h3>
                        <p>20+ products</p>
                    </a>
                </div>
            </div>

            <!-- Offer strip removed -->

            <section class="about-us" id="about">
                <div class="about-text">
                    <p class="subtitle">Some words about us</p>
                    <h1>We Help Everyone Enjoy Amazing Products</h1>
                    <p class="description">If the copy becomes distracting in the design then you are doing something
                        wrong or they are discussing copy changes. It might be a bit annoying but you could tell them
                        that that discussion would be best suited.</p>
                </div>
                <div class="about-images">
                    <div class="image-box">
                        <img src="./Assets/Images/a.jpg" alt="Team">
                    </div>
                    <div class="testimonial-box">
                        <p>Websites in professional use templating systems. Commercial publishing platforms and content
                            management systems ensure that you can show.</p>
                        <div class="testimonial-author">
                            <img src="./Assets/Images/accessories-inst-6.jpg" alt="Author">
                            <div>
                                <h4>Fathima Athiyya</h4>
                                <p>SKR pvt ltd.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="featured-products">
                <h2>Featured Products</h2>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <?php
                        $img = preg_replace('/^\.\.\//', '', $product['image_url']);
                        $base = (float) ($product['base_min_price'] ?? 0);
                        ?>
                        <div class="product-box">
                            <div style="position:relative">
                                <img src="<?php echo htmlspecialchars($img); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">Rs.<?php echo number_format($base, 2); ?></p>
                            <a href="product_details.php?id=<?php echo (int) $product['product_id']; ?>"
                                class="btn btn-primary">Buy Now</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="faq">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-item">
                    <h4 class="faq-question">How can I track my order?</h4>
                    <div class="faq-answer">
                        <p>Once your order is shipped, we email you a tracking number to monitor its progress.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <h4 class="faq-question">What payment methods do you accept?</h4>
                    <div class="faq-answer">
                        <p>We accept all major credit cards, PayPal, and Apple Pay.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <h4 class="faq-question">What is your return policy?</h4>
                    <div class="faq-answer">
                        <p>Returns accepted within 30 days if the product is in original condition.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <h4 class="faq-question">Do you offer international shipping?</h4>
                    <div class="faq-answer">
                        <p>Yes, we ship internationally. Fees and times depend on your location.</p>
                    </div>
                </div>
            </section>

            <!-- New Arrivals -->
            <section class="new-arrivals">
                <h2>New Arrivals</h2>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <?php
                        $img = preg_replace('/^\.\.\//', '', $product['image_url']);
                        $base = (float) ($product['base_min_price'] ?? 0);
                        ?>
                        <div class="product-box">
                            <div style="position:relative">
                                <img src="<?php echo htmlspecialchars($img); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">Rs.<?php echo number_format($base, 2); ?></p>
                            <a class="btn btn-primary"
                                href="product_details.php?id=<?php echo (int) $product['product_id']; ?>">View</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>


            <!-- Testimonials -->
            <section class="testimonials">
                <h2>What People Say</h2>
                <div class="testimonial-grid">
                    <div class="testimonial-card">
                        <p>“Lightning-fast delivery and genuine Apple gear. Loved the experience!”</p>
                        <div class="author">— Ahamed R.</div>
                    </div>
                    <div class="testimonial-card">
                        <p>“Great prices and service. My go-to store for accessories.”</p>
                        <div class="author">— Nivetha K.</div>
                    </div>
                    <div class="testimonial-card">
                        <p>“Smooth checkout and quality products. Highly recommended.”</p>
                        <div class="author">— Dinesh P.</div>
                    </div>
                </div>
            </section>

            <!-- Newsletter -->
            <section class="newsletter" id="newsletter">
                <h2>Stay in the loop</h2>
                <p>Subscribe for new arrivals, deals and more.</p>
                <form action="subscribe.php" method="post" class="newsletter-form">
                    <input type="email" name="email" placeholder="Your email address" required>
                    <input type="hidden" name="redirect"
                        value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                    <button type="submit" class="btn">Subscribe</button>
                </form>
                <?php if (!empty($_SESSION['newsletter_success'])): ?>
                    <div class="flash success">
                        <?php echo $_SESSION['newsletter_success'];
                        unset($_SESSION['newsletter_success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($_SESSION['newsletter_error'])): ?>
                    <div class="flash error">
                        <?php echo $_SESSION['newsletter_error'];
                        unset($_SESSION['newsletter_error']); ?>
                    </div>
                <?php endif; ?>
            </section>


            <!-- Brand Runway: dual angled, counter-scrolling strips -->
            <section class="brand-runway" aria-label="Brands connected with Apple">
                <!-- Strip A (moves left) -->
                <div class="strip strip-a">
                    <div class="track" role="list">
                        <div class="logos" role="group">
                            <img src="https://cdn.simpleicons.org/apple/111" alt="Apple" role="listitem" />
                            <img src="https://cdn.simpleicons.org/beats/111" alt="Beats by Dre" role="listitem" />
                            <img src="https://cdn.simpleicons.org/nike/111" alt="Nike" role="listitem" />
                            <img src="https://cdn.simpleicons.org/hermes/111" alt="Hermès" role="listitem" />
                            <img src="https://cdn.simpleicons.org/corning/111" alt="Corning" role="listitem" />
                            <img src="https://cdn.simpleicons.org/arm/111" alt="ARM" role="listitem" />
                            <img src="https://cdn.simpleicons.org/qualcomm/111" alt="Qualcomm" role="listitem" />
                            <img src="https://cdn.simpleicons.org/broadcom/111" alt="Broadcom" role="listitem" />
                            <img src="https://cdn.simpleicons.org/intel/111" alt="Intel" role="listitem" />
                            <img src="https://cdn.simpleicons.org/tsmc/111" alt="TSMC" role="listitem" />
                            <img src="https://cdn.simpleicons.org/samsung/111" alt="Samsung Display" role="listitem" />
                            <img src="https://cdn.simpleicons.org/lg/111" alt="LG" role="listitem" />
                            <img src="https://cdn.simpleicons.org/sony/111" alt="Sony" role="listitem" />
                            <img src="https://cdn.simpleicons.org/belkin/111" alt="Belkin" role="listitem" />
                            <img src="https://cdn.simpleicons.org/bose/111" alt="Bose" role="listitem" />
                        </div>
                        <div class="logos" aria-hidden="true">
                            <img src="https://cdn.simpleicons.org/apple/111" alt="" />
                            <img src="https://cdn.simpleicons.org/beats/111" alt="" />
                            <img src="https://cdn.simpleicons.org/nike/111" alt="" />
                            <img src="https://cdn.simpleicons.org/hermes/111" alt="" />
                            <img src="https://cdn.simpleicons.org/corning/111" alt="" />
                            <img src="https://cdn.simpleicons.org/arm/111" alt="" />
                            <img src="https://cdn.simpleicons.org/qualcomm/111" alt="" />
                            <img src="https://cdn.simpleicons.org/broadcom/111" alt="" />
                            <img src="https://cdn.simpleicons.org/intel/111" alt="" />
                            <img src="https://cdn.simpleicons.org/tsmc/111" alt="" />
                            <img src="https://cdn.simpleicons.org/samsung/111" alt="" />
                            <img src="https://cdn.simpleicons.org/lg/111" alt="" />
                            <img src="https://cdn.simpleicons.org/sony/111" alt="" />
                            <img src="https://cdn.simpleicons.org/belkin/111" alt="" />
                            <img src="https://cdn.simpleicons.org/bose/111" alt="" />
                        </div>
                    </div>
                </div>

                <!-- Strip B (moves right) -->
                <div class="strip strip-b">
                    <div class="track" role="list">
                        <div class="logos" role="group">
                            <img src="https://cdn.simpleicons.org/apple/111" alt="Apple" role="listitem" />
                            <img src="https://cdn.simpleicons.org/beats/111" alt="Beats by Dre" role="listitem" />
                            <img src="https://cdn.simpleicons.org/nike/111" alt="Nike" role="listitem" />
                            <img src="https://cdn.simpleicons.org/hermes/111" alt="Hermès" role="listitem" />
                            <img src="https://cdn.simpleicons.org/corning/111" alt="Corning" role="listitem" />
                            <img src="https://cdn.simpleicons.org/arm/111" alt="ARM" role="listitem" />
                            <img src="https://cdn.simpleicons.org/qualcomm/111" alt="Qualcomm" role="listitem" />
                            <img src="https://cdn.simpleicons.org/broadcom/111" alt="Broadcom" role="listitem" />
                            <img src="https://cdn.simpleicons.org/intel/111" alt="Intel" role="listitem" />
                            <img src="https://cdn.simpleicons.org/tsmc/111" alt="TSMC" role="listitem" />
                            <img src="https://cdn.simpleicons.org/samsung/111" alt="Samsung Display" role="listitem" />
                            <img src="https://cdn.simpleicons.org/lg/111" alt="LG" role="listitem" />
                            <img src="https://cdn.simpleicons.org/sony/111" alt="Sony" role="listitem" />
                            <img src="https://cdn.simpleicons.org/belkin/111" alt="Belkin" role="listitem" />
                            <img src="https://cdn.simpleicons.org/bose/111" alt="Bose" role="listitem" />
                        </div>
                        <div class="logos" aria-hidden="true">
                            <img src="https://cdn.simpleicons.org/apple/111" alt="" />
                            <img src="https://cdn.simpleicons.org/beats/111" alt="" />
                            <img src="https://cdn.simpleicons.org/nike/111" alt="" />
                            <img src="https://cdn.simpleicons.org/hermes/111" alt="" />
                            <img src="https://cdn.simpleicons.org/corning/111" alt="" />
                            <img src="https://cdn.simpleicons.org/arm/111" alt="" />
                            <img src="https://cdn.simpleicons.org/qualcomm/111" alt="" />
                            <img src="https://cdn.simpleicons.org/broadcom/111" alt="" />
                            <img src="https://cdn.simpleicons.org/intel/111" alt="" />
                            <img src="https://cdn.simpleicons.org/tsmc/111" alt="" />
                            <img src="https://cdn.simpleicons.org/samsung/111" alt="" />
                            <img src="https://cdn.simpleicons.org/lg/111" alt="" />
                            <img src="https://cdn.simpleicons.org/sony/111" alt="" />
                            <img src="https://cdn.simpleicons.org/belkin/111" alt="" />
                            <img src="https://cdn.simpleicons.org/bose/111" alt="" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats -->
            <section class="stats">
                <div class="stat">
                    <div class="emoji" aria-hidden="true">🚚</div>
                    <div class="num">10k+</div>
                    <div class="label">Orders Delivered</div>
                </div>
                <div class="stat">
                    <div class="emoji" aria-hidden="true">🌟</div>
                    <div class="num">4.9</div>
                    <div class="label">Average Rating</div>
                </div>
                <div class="stat">
                    <div class="emoji" aria-hidden="true">🎧</div>
                    <div class="num">24/7</div>
                    <div class="label">Support</div>
                </div>
                <div class="stat">
                    <div class="emoji" aria-hidden="true">⏱️</div>
                    <div class="num">99.9%</div>
                    <div class="label">On‑time Delivery</div>
                </div>
            </section>

            <!-- Blog Teasers -->
            <section class="blog-teasers">
                <h2>From Our Blog</h2>
                <div class="blog-grid">
                    <article class="blog-card">
                        <h3>Choosing the Right MacBook in 2025</h3>
                        <p>We break down M-series models, RAM choices, and SSD trade-offs…</p>
                        <a class="btn btn-secondary" href="#">Read More</a>
                    </article>
                    <article class="blog-card">
                        <h3>Must-have iPhone Accessories</h3>
                        <p>Cases, chargers, and earbuds that are worth your money…</p>
                        <a class="btn btn-secondary" href="#">Read More</a>
                    </article>
                    <article class="blog-card">
                        <h3>Apple Watch Tips & Tricks</h3>
                        <p>Optimize battery life and fitness tracking with these tips…</p>
                        <a class="btn btn-secondary" href="#">Read More</a>
                    </article>
                </div>
            </section>

            <!-- Final CTA -->
            <section class="final-cta">
                <h2>Ready to upgrade?</h2>
                <a class="btn btn-primary" href="products.php">Shop Now</a>
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

    <?php include 'login_modal_include.php'; ?>
    <script src="./Assets/Js/sidebar.js"></script>
    <script src="./Assets/Js/hero.js"></script>
    <script src="./Assets/Js/modal.js"></script>
    <script src="./Assets/Js/auth.js"></script>
    <script>
        // Toggle FAQ answers
        document.querySelectorAll('.faq-question').forEach(function (item) {
            item.addEventListener('click', function () {
                var ans = item.nextElementSibling;
                if (!ans) return;
                ans.style.display = ans.style.display === 'block' ? 'none' : 'block';
                item.classList.toggle('active');
            });
        });
    </script>
</body>

</html>