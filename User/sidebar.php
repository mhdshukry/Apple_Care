<div class="sidebar">
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
            <a href="cart.php">
                <i class="fas fa-cart-plus"></i>
                <span>Add to Cart</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'about') ? 'active' : ''; ?>">
            <a href="./about.php">
                <i class="fa fa-user"></i>
                <span>About Us</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'contact') ? 'active' : ''; ?>">
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
