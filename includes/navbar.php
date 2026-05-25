<?php
// navbar.php - Desktop Top Navbar Include
?>
<!-- Desktop Navbar -->
<nav class="navbar navbar-expand-lg desktop-navbar sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="assets/images/logo.svg" alt="<?php echo htmlspecialchars(get_setting('site_name', 'MyStore')); ?>" height="40" class="navbar-logo">
        </a>
        
        <!-- Navbar Items -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-500" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-500" href="products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-500" href="contact.php">Support & FAQs</a>
                </li>
            </ul>
            
            <!-- Navbar Action Controls -->
            <div class="d-flex align-items-center gap-3">
                <!-- Theme Switcher -->
                <button class="theme-toggle-btn btn shadow-0 p-0" aria-label="Toggle Theme">
                    <i class="fas fa-moon text-primary"></i>
                </button>
                
                <!-- Shopping Cart Icon -->
                <a href="cart.php" class="position-relative p-2 text-reset" aria-label="View Cart">
                    <i class="fas fa-shopping-bag fa-lg text-primary"></i>
                    <span class="badge rounded-pill badge-notification bg-danger cart-badge" style="display: none;">0</span>
                </a>
                
                <!-- Authentication Status -->
                <?php if (is_logged_in()): 
                    $user = get_logged_in_user();
                ?>
                    <!-- User Account Dropdown -->
                    <div class="dropdown">
                        <a class="dropdown-toggle d-flex align-items-center text-reset fw-bold" href="#" id="navbarDropdownMenuLink" role="button" data-mdb-toggle="dropdown" aria-expanded="false">
                            <span class="me-1"><i class="fas fa-user-circle fa-lg me-1 text-primary"></i><?php echo htmlspecialchars($user['name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="navbarDropdownMenuLink">
                            <li>
                                <a class="dropdown-item" href="profile.php"><i class="fas fa-id-card me-2"></i>My Profile</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="orders.php"><i class="fas fa-box-open me-2"></i>My Orders</a>
                            </li>
                            <?php if (has_role(['admin', 'editor'])): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item fw-bold text-gradient" href="admin/index.php"><i class="fas fa-chart-line me-2"></i>Admin Dashboard</a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-link text-reset px-3 fw-bold">Login</a>
                    <a href="signup.php" class="btn btn-premium">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Sticky AppBar -->
<header class="mobile-appbar sticky-top py-2 px-3 justify-content-between align-items-center">
    <a class="d-flex align-items-center text-reset" href="index.php">
        <img src="assets/images/logo.svg" alt="<?php echo htmlspecialchars(get_setting('site_name', 'MyStore')); ?>" height="34" class="navbar-logo">
    </a>
    <div class="d-flex align-items-center gap-3">
        <!-- Theme Toggle -->
        <button class="theme-toggle-btn btn shadow-0 p-0" aria-label="Toggle Theme">
            <i class="fas fa-moon text-primary"></i>
        </button>
        <!-- Quick Cart -->
        <a href="cart.php" class="position-relative p-2 text-reset" aria-label="View Cart">
            <i class="fas fa-shopping-bag fa-lg text-primary"></i>
            <span class="badge rounded-pill badge-notification bg-danger cart-badge" style="display: none;">0</span>
        </a>
    </div>
</header>
