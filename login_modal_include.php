<?php // Reusable Auth Modals: include on pages that need Sign In / Sign Up ?>

<!-- Sign In Modal -->
<div id="loginModal" class="modal">
    <div class="auth-card">
        <aside class="auth-aside">
            <div class="logo">
                <img src="./Assets/Images/apple.png" alt="Logo">
                <strong>Apple Care+</strong>
            </div>
            <h2>Welcome back 👋</h2>
            <p>Sign in to track orders, review purchases, and unlock member deals.</p>
            <ul class="auth-perks">
                <li>• Fast checkout</li>
                <li>• Order history</li>
                <li>• Exclusive offers</li>
            </ul>
        </aside>
        <section class="auth-form">
            <div class="auth-header">
                <h3>Sign In</h3>
                <span class="close" onclick="closeModal('loginModal')">&times;</span>
            </div>

            <div class="socials">
                <a class="social-btn" href="#"><i class="fab fa-google"></i> Google</a>
                <a class="social-btn" href="#"><i class="fab fa-apple"></i> Apple</a>
            </div>
            <div class="divider">Or continue with email</div>

            <form action="login.php" method="post">
                <div class="input-group">
                    <label class="input-label" for="login-email">Email</label>
                    <div class="input-wrap">
                        <input id="login-email" type="email" name="email" class="input-field"
                            placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label" for="login-pass">Password</label>
                    <div class="input-wrap">
                        <input id="login-pass" type="password" name="password" class="input-field"
                            placeholder="Your password" required>
                        <i class="fas fa-eye toggle-pass" title="Show/Hide" data-toggle-pass></i>
                    </div>
                </div>

                <div class="options">
                    <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
                    <div class="forgot"><a href="#">Forgot password?</a></div>
                </div>

                <button type="submit" class="btn-auth">Sign In</button>
            </form>

            <p class="switch">New here? <a href="#" data-open-modal="signupModal">Create an account</a></p>
        </section>
    </div>
</div>

<!-- Sign Up Modal -->
<div id="signupModal" class="modal">
    <div class="auth-card">
        <aside class="auth-aside">
            <div class="logo">
                <img src="./Assets/Images/apple.png" alt="Logo">
                <strong>Apple Care+</strong>
            </div>
            <h2>Join the club ✨</h2>
            <p>Create your account to enjoy faster checkout and personalized offers.</p>
            <ul class="auth-perks">
                <li>• Save favorites</li>
                <li>• Member pricing</li>
                <li>• Early access</li>
            </ul>
        </aside>
        <section class="auth-form">
            <div class="auth-header">
                <h3>Sign Up</h3>
                <span class="close" onclick="closeModal('signupModal')">&times;</span>
            </div>

            <div class="socials">
                <a class="social-btn" href="#"><i class="fab fa-google"></i> Google</a>
                <a class="social-btn" href="#"><i class="fab fa-apple"></i> Apple</a>
            </div>
            <div class="divider">Or continue with email</div>

            <form action="signup.php" method="post">
                <div class="input-group">
                    <label class="input-label" for="signup-username">Username</label>
                    <div class="input-wrap">
                        <input id="signup-username" type="text" name="username" class="input-field"
                            placeholder="Your name" required>
                    </div>
                </div>
                <div class="input-group">
                    <label class="input-label" for="signup-email">Email</label>
                    <div class="input-wrap">
                        <input id="signup-email" type="email" name="email" class="input-field"
                            placeholder="you@example.com" required>
                    </div>
                </div>
                <div class="input-group">
                    <label class="input-label" for="signup-pass">Password</label>
                    <div class="input-wrap">
                        <input id="signup-pass" type="password" name="password" class="input-field"
                            placeholder="Create a password" required>
                        <i class="fas fa-eye toggle-pass" title="Show/Hide" data-toggle-pass></i>
                    </div>
                </div>
                <button type="submit" class="btn-auth">Create Account</button>
            </form>

            <p class="switch">Already have an account? <a href="#" data-open-modal="loginModal">Sign in</a></p>
        </section>
    </div>
</div>