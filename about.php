<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Apple Care+</title>
    <link rel="stylesheet" href="./Assets/CSS/style.css" />
    <link rel="stylesheet" href="./Assets/CSS/auth.css" />
    <link rel="icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png" />
    <link rel="shortcut icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap"
        rel="stylesheet">
</head>

<body class="about-page">
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="./Assets/Images/apple.png" alt="Logo" />
                <span>Apple Care+</span>
            </div>
            <ul>
                <li>
                    <a href="./Index.php">
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
                <li class="active">
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

        <header class="header">
            <div class="news">Already have an account?</div>
            <div class="auth-buttons">
                <button class="btn" onclick="openModal('loginModal')">Sign In</button>
                <button class="btn" onclick="openModal('signupModal')">Sign Up</button>
            </div>
        </header>

        <div class="main-content">
            <!-- About Hero / Intro -->
            <section class="about-us" id="about">
                <div class="about-text">
                    <p class="subtitle">Who we are</p>
                    <h1>About Apple Care+</h1>
                    <p class="description">We bring you the best of Apple and premium accessories with fast delivery,
                        expert support, and a great shopping experience. Our mission is to make top‑tier tech more
                        accessible and more delightful for everyone.</p>
                </div>
                <div class="about-images">
                    <div class="image-box">
                        <img src="./Assets/Images/a.jpg" alt="Our Store" />
                    </div>
                    <div class="testimonial-box">
                        <p>“We obsess over details so our customers don’t have to.”</p>
                        <div class="testimonial-author">
                            <img src="https://i.pravatar.cc/84?img=12" alt="Author" />
                            <div>
                                <h4>Team Apple_Care</h4>
                                <p>Customer First • Quality Always</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Achievements (modern glass badges) -->
            <section class="achievements-modern" aria-label="Achievements">
                <h2>Achievements</h2>
                <p class="ach-subtitle">Milestones that reflect our quality, speed, and service.</p>
                <div class="ach-grid">
                    <article class="ach-card t-gold">
                        <span class="ach-ribbon">2025</span>
                        <div class="ach-emblem" aria-hidden="true">
                            <img class="ach-icon" src="https://cdn.simpleicons.org/apple/ffffff" alt="" />
                        </div>
                        <h3>Premium Partner</h3>
                        <p>Recognized for top‑tier experience and genuine products.</p>
                    </article>
                    <article class="ach-card t-silver">
                        <span class="ach-ribbon">Express</span>
                        <div class="ach-emblem" aria-hidden="true">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3>Ultra‑Fast Dispatch</h3>
                        <p>Most orders ship within 24 hours with live tracking.</p>
                    </article>
                    <article class="ach-card t-iris">
                        <span class="ach-ribbon">Genuine</span>
                        <div class="ach-emblem" aria-hidden="true">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Verified Authenticity</h3>
                        <p>Backed by trusted partners and quality checks.</p>
                    </article>
                    <article class="ach-card t-teal">
                        <span class="ach-ribbon">500+</span>
                        <div class="ach-emblem" aria-hidden="true">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3>Curated Catalog</h3>
                        <p>A growing lineup of devices and accessories.</p>
                    </article>
                    <article class="ach-card t-rose">
                        <span class="ach-ribbon">24/7</span>
                        <div class="ach-emblem" aria-hidden="true">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Expert Support</h3>
                        <p>Real guidance and after‑sales help whenever you need it.</p>
                    </article>
                </div>
            </section>

            <!-- Values (modern 5-card) -->
            <section class="values-section">
                <h2>Our Values</h2>
                <div class="values-grid">
                    <article class="value-card">
                        <div class="value-icon" aria-hidden="true">🤝</div>
                        <h3 class="value-title">Customer Obsession</h3>
                        <p class="value-desc">We design every step around your needs—support, speed, and satisfaction.
                        </p>
                    </article>
                    <article class="value-card">
                        <div class="value-icon" aria-hidden="true">🛡️</div>
                        <h3 class="value-title">Genuine Quality</h3>
                        <p class="value-desc">Only authentic products and vetted partners make the cut.</p>
                    </article>
                    <article class="value-card">
                        <div class="value-icon" aria-hidden="true">⚡</div>
                        <h3 class="value-title">Fast Delivery</h3>
                        <p class="value-desc">Swift logistics and reliable tracking from checkout to doorstep.</p>
                    </article>
                    <article class="value-card">
                        <div class="value-icon" aria-hidden="true">🌱</div>
                        <h3 class="value-title">Sustainability</h3>
                        <p class="value-desc">Eco‑friendly choices in packaging and processes wherever possible.</p>
                    </article>
                    <article class="value-card">
                        <div class="value-icon" aria-hidden="true">💬</div>
                        <h3 class="value-title">Real Support</h3>
                        <p class="value-desc">Honest, helpful guidance from a team that truly cares.</p>
                    </article>
                </div>
            </section>

            <!-- Journey / Timeline (modern) -->
            <section class="timeline-section">
                <h2>Our Journey</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-card">
                            <div class="timeline-badge">🏁</div>
                            <img loading="lazy"
                                src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1200&auto=format&fit=crop"
                                alt="Launch" />
                            <div class="timeline-meta">
                                <span class="timeline-year">2021</span>
                                <h3>Store Launch</h3>
                                <p>Started with curated Apple devices and essential genuine accessories.</p>
                            </div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-card">
                            <div class="timeline-badge">🚀</div>
                            <img loading="lazy"
                                src="https://images.unsplash.com/photo-1529101091764-c3526daf38fe?q=80&w=1200&auto=format&fit=crop"
                                alt="Faster shipping" />
                            <div class="timeline-meta">
                                <span class="timeline-year">2022</span>
                                <h3>Faster Shipping</h3>
                                <p>Optimized logistics for quicker deliveries and better tracking.</p>
                            </div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-card">
                            <div class="timeline-badge">🌍</div>
                            <img loading="lazy"
                                src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1200&auto=format&fit=crop"
                                alt="Trusted partners" />
                            <div class="timeline-meta">
                                <span class="timeline-year">2023</span>
                                <h3>Trusted Partners</h3>
                                <p>Expanded our catalog with more Apple‑ready brands and options.</p>
                            </div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-card">
                            <div class="timeline-badge">🏆</div>
                            <img loading="lazy"
                                src="https://images.unsplash.com/photo-1545235617-9465d2a55698?q=80&w=1200&auto=format&fit=crop"
                                alt="Customer favorite" />
                            <div class="timeline-meta">
                                <span class="timeline-year">2024–25</span>
                                <h3>Customer Favorite</h3>
                                <p>Top‑rated for genuine products, pricing, and friendly support.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Team (modern 4-card) -->
            <section class="team-section">
                <h2>Meet the Team</h2>
                <div class="team-grid">
                    <article class="team-card">
                        <img class="team-avatar"
                            src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=400&auto=format&fit=crop"
                            alt="Aisha Kapoor" />
                        <h3 class="team-name">Aisha Kapoor</h3>
                        <span class="role-chip">Head of CX</span>
                        <p class="team-bio">Champions customer delight and service excellence across channels.</p>
                        <div class="team-socials">
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </article>
                    <article class="team-card">
                        <img class="team-avatar"
                            src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=400&auto=format&fit=crop"
                            alt="Dev Patel" />
                        <h3 class="team-name">Dev Patel</h3>
                        <span class="role-chip">Product Lead</span>
                        <p class="team-bio">Curates a catalog that balances performance, value, and style.</p>
                        <div class="team-socials">
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </article>
                    <article class="team-card">
                        <img class="team-avatar"
                            src="https://images.unsplash.com/photo-1547425260-76bcadfb4f2c?q=80&w=400&auto=format&fit=crop"
                            alt="Meera Iyer" />
                        <h3 class="team-name">Meera Iyer</h3>
                        <span class="role-chip">Operations</span>
                        <p class="team-bio">Keeps logistics running smoothly with precision and care.</p>
                        <div class="team-socials">
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </article>
                    <article class="team-card">
                        <img class="team-avatar"
                            src="https://images.unsplash.com/photo-1547425260-76bcadfb4f2c?q=80&w=400&auto=format&fit=crop"
                            alt="Arjun Rao" />
                        <h3 class="team-name">Arjun Rao</h3>
                        <span class="role-chip">Logistics</span>
                        <p class="team-bio">Ensures on‑time delivery and clear tracking every step of the way.</p>
                        <div class="team-socials">
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Partners (brand strip using internet icons) -->
            <section class="brand-strip" aria-label="Partner Brands">
                <img src="https://cdn.simpleicons.org/apple/111" alt="Apple" />
                <img src="https://cdn.simpleicons.org/beats/111" alt="Beats by Dre" />
                <img src="https://cdn.simpleicons.org/corning/111" alt="Corning" />
                <img src="https://cdn.simpleicons.org/arm/111" alt="ARM" />
                <img src="https://cdn.simpleicons.org/qualcomm/111" alt="Qualcomm" />
                <img src="https://cdn.simpleicons.org/intel/111" alt="Intel" />
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

            <!-- FAQ -->
            <section class="faq">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-item">
                    <h4 class="faq-question">Are your products genuine?</h4>
                    <div class="faq-answer">
                        <p>Yes—only authentic products from trusted partners.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <h4 class="faq-question">How long does delivery take?</h4>
                    <div class="faq-answer">
                        <p>Typically 3–5 business days depending on location.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <h4 class="faq-question">Do you ship internationally?</h4>
                    <div class="faq-answer">
                        <p>Yes, international options are available at checkout.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <h4 class="faq-question">What if I need help deciding?</h4>
                    <div class="faq-answer">
                        <p>Our support team is here to help with honest recommendations.</p>
                    </div>
                </div>
            </section>

            <!-- Newsletter -->
            <section class="newsletter" id="newsletter">
                <h2>Stay in the loop</h2>
                <p>Subscribe for new arrivals, deals and more.</p>
                <form action="subscribe.php" method="post" class="newsletter-form">
                    <input type="email" name="email" placeholder="Your email address" required />
                    <input type="hidden" name="redirect"
                        value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" />
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

            <!-- Final CTA -->
            <section class="final-cta">
                <h2>Ready to explore products?</h2>
                <a class="btn btn-primary" href="products.php">Shop Now</a>
            </section>
        </div>
    </div>

    <?php include 'login_modal_include.php'; ?>

    <script src="./Assets/Js/sidebar.js"></script>
    <script src="./Assets/Js/modal.js"></script>
    <script src="./Assets/Js/auth.js"></script>
    <script>
        document.querySelectorAll('.faq-question').forEach(function (item) {
            item.addEventListener('click', function () {
                var ans = item.nextElementSibling; if (!ans) return; ans.style.display = ans.style.display === 'block' ? 'none' : 'block'; item.classList.toggle('active');
            });
        });
    </script>
</body>

</html>