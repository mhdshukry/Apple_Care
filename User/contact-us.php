<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Apple Care+</title>
    <link rel="icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png" />
    <link rel="shortcut icon" type="image/png" href="/Apple_Care/Assets/Images/apple.png" />
    <link rel="stylesheet" href="../Assets/CSS/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap"
        rel="stylesheet">
</head>

<body class="contact-page">
    <div class="container">
        <?php $current_page = 'contact';
        include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Hero -->
            <section class="contact-hero">
                <div class="hero-text">
                    <h1>Let’s talk</h1>
                    <p>Questions about products, orders, or returns? We’re here to help.</p>
                    <div class="hero-tags">
                        <span>Support</span><span>Orders</span><span>Returns</span><span>Warranty</span>
                    </div>
                </div>
                <div class="hero-media">
                    <img src="https://images.unsplash.com/photo-1529333166437-7750a6dd5a70?q=80&w=1600&auto=format&fit=crop"
                        alt="Contact" />
                </div>
            </section>

            <!-- Quick Cards -->
            <section class="contact-cards">
                <article class="c-card"><i class="fas fa-headset"></i>
                    <h3>Chat with us</h3>
                    <p>Reach our team for quick help.</p>
                </article>
                <article class="c-card"><i class="fas fa-envelope"></i>
                    <h3>Email support</h3>
                    <p>We reply within 24 hours.</p>
                </article>
                <article class="c-card"><i class="fas fa-shipping-fast"></i>
                    <h3>Order help</h3>
                    <p>Tracking, returns, and more.</p>
                </article>
                <article class="c-card"><i class="fas fa-store"></i>
                    <h3>Visit store</h3>
                    <p>Find our nearest outlet.</p>
                </article>
            </section>

            <!-- Smart Help: search + topic chips -->
            <section class="smart-help">
                <h2>How can we help?</h2>
                <div class="help-search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="text" placeholder="Search help topics (e.g., returns, warranty, invoices)"
                        aria-label="Search help topics" />
                </div>
                <div class="topic-chips">
                    <a href="#" class="chip">Orders</a>
                    <a href="#" class="chip">Returns</a>
                    <a href="#" class="chip">Warranty</a>
                    <a href="#" class="chip">Billing</a>
                    <a href="#" class="chip">Account</a>
                    <a href="#" class="chip">Store Pickup</a>
                </div>
            </section>

            <!-- Quick Portals: modern CTAs -->
            <section class="portal-cta">
                <div class="portal-grid">
                    <a class="portal-card p1" href="#">
                        <div class="pc-icon"><i class="fas fa-shipping-fast"></i></div>
                        <h3>Track your order</h3>
                        <p>Live status, ETA, and delivery updates.</p>
                        <span class="pc-arrow">→</span>
                    </a>
                    <a class="portal-card p2" href="#">
                        <div class="pc-icon"><i class="fas fa-undo-alt"></i></div>
                        <h3>Returns portal</h3>
                        <p>Start a return or exchange in minutes.</p>
                        <span class="pc-arrow">→</span>
                    </a>
                    <a class="portal-card p3" href="#">
                        <div class="pc-icon"><i class="fas fa-tools"></i></div>
                        <h3>Book a repair</h3>
                        <p>Check coverage and schedule service.</p>
                        <span class="pc-arrow">→</span>
                    </a>
                </div>
            </section>

            <!-- Service Assurances: modern badges instead of hours -->
            <section class="service-assurances">
                <div class="assurance-grid">
                    <div class="assurance-card a1">
                        <div class="a-icon">⏱️</div>
                        <div>
                            <h4>~2h</h4>
                            <p>Average reply</p>
                        </div>
                    </div>
                    <div class="assurance-card a2">
                        <div class="a-icon">🚚</div>
                        <div>
                            <h4>24h</h4>
                            <p>Fast dispatch</p>
                        </div>
                    </div>
                    <div class="assurance-card a3">
                        <div class="a-icon">🔁</div>
                        <div>
                            <h4>7 days</h4>
                            <p>Easy returns</p>
                        </div>
                    </div>
                    <div class="assurance-card a4">
                        <div class="a-icon">🛡️</div>
                        <div>
                            <h4>Genuine</h4>
                            <p>Warranty support</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Help Topics (full-width, colorful) -->
            <section class="help-topics-wide">
                <div class="help-head">
                    <h2>Help topics</h2>
                    <p>Quick answers on orders, returns, warranty, and payments.</p>
                </div>
                <div class="help-grid colorful">
                    <article class="help-card g1">
                        <div class="hc-icon"><i class="fas fa-box"></i></div>
                        <h3>Orders & Shipping</h3>
                        <p>Track orders, delivery timelines, shipping fees, and pickup.</p>
                        <div class="hc-actions"><a href="#" class="chip">Tracking</a><a href="#"
                                class="chip">Express</a></div>
                    </article>
                    <article class="help-card g2">
                        <div class="hc-icon"><i class="fas fa-undo-alt"></i></div>
                        <h3>Returns & Refunds</h3>
                        <p>Initiate returns, exchange eligibility, refunds, and timelines.</p>
                        <div class="hc-actions"><a href="#" class="chip">Return</a><a href="#" class="chip">Exchange</a>
                        </div>
                    </article>
                    <article class="help-card g3">
                        <div class="hc-icon"><i class="fas fa-shield-alt"></i></div>
                        <h3>Warranty & Repairs</h3>
                        <p>What’s covered, claim steps, repair options, and service slots.</p>
                        <div class="hc-actions"><a href="#" class="chip">Check coverage</a><a href="#" class="chip">Book
                                repair</a></div>
                    </article>
                    <article class="help-card g4">
                        <div class="hc-icon"><i class="fas fa-credit-card"></i></div>
                        <h3>Payments & Billing</h3>
                        <p>Supported methods, EMI, invoices, and payment troubleshooting.</p>
                        <div class="hc-actions"><a href="#" class="chip">EMI</a><a href="#" class="chip">Invoices</a>
                        </div>
                    </article>
                    <article class="help-card g5">
                        <div class="hc-icon"><i class="fas fa-user-shield"></i></div>
                        <h3>Account & Security</h3>
                        <p>Manage account details, passwords, and sign‑in security.</p>
                        <div class="hc-actions"><a href="#" class="chip">Password</a><a href="#" class="chip">2FA</a>
                        </div>
                    </article>
                    <article class="help-card g6">
                        <div class="hc-icon"><i class="fas fa-store"></i></div>
                        <h3>Store & Pickup</h3>
                        <p>Store hours, curbside pickup, and in‑store assistance.</p>
                        <div class="hc-actions"><a href="#" class="chip">Pickup</a><a href="#" class="chip">Hours</a>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Branch Map (interactive) -->
            <section class="map-section">
                <h2>Find our branches</h2>
                <div id="branch-map" aria-label="Branch locations"></div>
            </section>

            <!-- Locations / Map (image placeholder) -->
            <section class="contact-locations">
                <h2>Our locations</h2>
                <div class="loc-grid">
                    <article class="loc-card">
                        <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?q=80&w=1200&auto=format&fit=crop"
                            alt="Colombo" />
                        <div class="loc-meta">
                            <h3>Colombo</h3>
                            <p>Flagship store & service</p>
                        </div>
                    </article>
                    <article class="loc-card">
                        <img src="https://images.unsplash.com/photo-1472214103451-9374bd1c798e?q=80&w=1200&auto=format&fit=crop"
                            alt="Kandy" />
                        <div class="loc-meta">
                            <h3>Kandy</h3>
                            <p>Retail & pickup point</p>
                        </div>
                    </article>
                    <article class="loc-card">
                        <img src="https://images.unsplash.com/photo-1504805572947-34fad45aed93?q=80&w=1200&auto=format&fit=crop"
                            alt="Galle" />
                        <div class="loc-meta">
                            <h3>Galle</h3>
                            <p>Service and support</p>
                        </div>
                    </article>
                </div>
            </section>

            <!-- FAQ CTA -->
            <section class="contact-cta">
                <div class="cta-card">
                    <h2>Need quick answers?</h2>
                    <p>Check our FAQ for shipping, returns, and warranty information.</p>
                    <a class="btn btn-primary" href="about.php#newsletter">Visit FAQ</a>
                </div>
            </section>

            <!-- Callback Request -->
            <section class="callback-section">
                <div class="callback-card">
                    <h2>Request a callback</h2>
                    <p>Prefer us to call you? Share your details and we’ll get back shortly.</p>
                    <form action="../contact.php" method="post" class="callback-form">
                        <div class="row">
                            <input type="text" name="name" placeholder="Your name" required />
                            <input type="email" name="email" placeholder="Email address" required />
                        </div>
                        <div class="row">
                            <input type="tel" name="phone" placeholder="Phone number" required />
                            <input type="text" name="subject" value="Callback Request" placeholder="Subject" />
                        </div>
                        <textarea name="message" rows="3" placeholder="Best time to reach you (optional)"></textarea>
                        <input type="hidden" name="redirect"
                            value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" />
                        <button type="submit" class="btn">Request call</button>
                    </form>
                </div>
            </section>

        </div>
    </div>

    <script src="../Assets/Js/sidebar.js"></script>
    <script src="../Assets/Js/modal.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        (function () {
            var mapEl = document.getElementById('branch-map');
            if (!mapEl) return;
            var map = L.map('branch-map');
            var markers = [
                { name: 'Colombo', pos: [6.9271, 79.8612], note: 'Flagship store & service' },
                { name: 'Kandy', pos: [7.2906, 80.6337], note: 'Retail & pickup point' },
                { name: 'Galle', pos: [6.0535, 80.2210], note: 'Service and support' }
            ];
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            var bounds = [];
            markers.forEach(function (m) {
                L.marker(m.pos).addTo(map).bindPopup('<strong>' + m.name + '</strong><br>' + m.note);
                bounds.push(m.pos);
            });
            map.fitBounds(bounds, { padding: [20, 20] });
        })();
    </script>
</body>

</html>