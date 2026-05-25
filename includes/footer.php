<?php
// footer.php - Global Footer Layout Include
?>
</main> <!-- Close Main Container -->

<!-- Responsive Footer Section -->
<footer class="text-center text-lg-start bg-body-tertiary text-muted mt-5 pt-4 border-top">
    <div class="container text-center text-md-start">
        <div class="row mt-3">
            <!-- Brand Column -->
            <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold mb-4 text-gradient">
                    <img src="assets/images/logo.svg" alt="My Store" height="30" class="navbar-logo">
                </h6>
                <p>
                    A premium digital asset store providing high-performance website templates, Flutter app codes, SaaS launch kits, and glassmorphic UI packages.
                </p>
            </div>
            
            <!-- Quick Links -->
            <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold mb-4">Products</h6>
                <p><a href="products.php?category=Templates" class="text-reset">Templates</a></p>
                <p><a href="products.php?category=Code" class="text-reset">Source Code</a></p>
                <p><a href="products.php?category=UI Kits" class="text-reset">UI & Design Kits</a></p>
            </div>
            
            <!-- Help/Support -->
            <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold mb-4">Useful Links</h6>
                <p><a href="contact.php" class="text-reset">Contact Support</a></p>
                <p><a href="profile.php" class="text-reset">My Profile</a></p>
                <p><a href="orders.php" class="text-reset">Track Purchases</a></p>
            </div>
            
            <!-- Legal/Certifications -->
            <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
                <h6 class="text-uppercase fw-bold mb-4">Secure Store</h6>
                <p><i class="fas fa-shield-alt me-3 text-success"></i>SSL Encrypted Checkouts</p>
                <p><i class="fas fa-cloud-download-alt me-3 text-info"></i>Secure Single-Use Downloads</p>
            </div>
        </div>
    </div>
    
    <!-- Copyright Bar -->
    <div class="text-center p-4 border-top" style="background-color: rgba(0, 0, 0, 0.02);">
        <?php echo get_setting('footer_text', '&copy; 2026 My Store. Sleek Solutions for Digital Creators.'); ?>
    </div>
</footer>

<!-- Include Mobile Bottom Navigation Bar -->
<?php include_once __DIR__ . '/bottom-nav.php'; ?>

<!-- MDBootstrap UI JS -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>

<!-- Core Main JS -->
<script src="assets/js/main.js"></script>
</body>
</html>
