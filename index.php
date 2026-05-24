<?php
// index.php - Store Landing / Home Page
require_once 'includes/header.php';

// Fetch Featured Products
$featured_products = [];
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY id DESC LIMIT 3");
    $featured_products = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail or default array
}

// Fetch FAQs
$faqs_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY id ASC LIMIT 5");
    $faqs_list = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
}
?>

<!-- 1. HERO SECTION -->
<div class="row align-items-center py-5 my-3 g-5">
    <div class="col-lg-6 text-center text-lg-start">
        <span class="badge bg-gradient-premium px-3 py-2 rounded-pill mb-3 shadow-0">STYLISH & 100% RESPONSIVE</span>
        <h1 class="display-3 fw-bold tracking-tight mb-4">
            Premium Digital Code <br>
            <span class="text-gradient">&amp; UI Assets</span>
        </h1>
        <p class="lead text-muted mb-5">
            Boost your product launches, Figma UI drafts, SaaS boilerplates, and custom cross-platform applications with curated, secure digital assets tailored for web creators.
        </p>
        <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
            <a href="products.php" class="btn btn-premium btn-lg py-3 px-5"><i class="fas fa-shopping-bag me-2"></i>Explore Products</a>
            <a href="contact.php" class="btn btn-outline-primary btn-lg rounded-pill py-3 px-4" style="border-width:2px;"><i class="fas fa-question-circle me-2"></i>Get Support</a>
        </div>
    </div>
    <div class="col-lg-6 text-center position-relative">
        <!-- Floating Glassmorphic Graphic representing high-tech product selling -->
        <div class="position-absolute top-50 start-50 translate-middle bg-gradient-premium rounded-circle opacity-10 blur-3xl" style="width: 350px; height: 350px; filter: blur(80px);"></div>
        <div class="card card-glass border-0 p-5 position-relative overflow-hidden hover-scale" style="background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));">
            <div class="py-4">
                <i class="fas fa-cubes fa-10x text-gradient mb-4"></i>
                <div class="d-flex justify-content-around mt-4">
                    <div class="text-center">
                        <h4 class="fw-bold mb-0">100%</h4>
                        <span class="small text-muted">Responsive</span>
                    </div>
                    <div class="border-end border-color"></div>
                    <div class="text-center">
                        <h4 class="fw-bold mb-0">Secure</h4>
                        <span class="small text-muted">Downloads</span>
                    </div>
                    <div class="border-end border-color"></div>
                    <div class="text-center">
                        <h4 class="fw-bold mb-0">3+</h4>
                        <span class="small text-muted">Gateways</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-5 opacity-10">

<!-- 2. FEATURED PRODUCTS SECTION -->
<div class="py-4">
    <div class="text-center mb-5">
        <span class="text-gradient fw-bold small text-uppercase tracking-widest"><i class="fas fa-star me-2"></i>Curated Selection</span>
        <h2 class="display-5 fw-bold mt-2">Featured Products</h2>
        <p class="text-muted col-md-6 mx-auto">Handpicked digital products engineered for efficiency, modern aesthetics, and performance.</p>
    </div>
    
    <div class="row g-4">
        <?php if (!empty($featured_products)): ?>
            <?php foreach ($featured_products as $p): 
                $screenshots = json_decode($p['screenshots'], true);
                $cover = (!empty($screenshots) && is_array($screenshots)) ? $screenshots[0] : 'assets/images/placeholder.webp';
            ?>
                <!-- Individual Product Card -->
                <div class="col-md-4">
                    <div class="card card-glass border-0 h-100 overflow-hidden d-flex flex-column justify-content-between">
                        <div>
                            <!-- Aspect ratio 16:9 for cover -->
                            <div class="bg-image hover-overlay ripple" data-mdb-ripple-color="light" style="height: 180px; overflow: hidden;">
                                <img src="<?php echo htmlspecialchars($cover); ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($p['title']); ?>" />
                                <a href="product-detail.php?id=<?php echo $p['id']; ?>">
                                    <div class="mask" style="background-color: rgba(251, 251, 251, 0.15);"></div>
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-primary px-3 py-2 rounded-pill"><?php echo htmlspecialchars($p['category']); ?></span>
                                    <h4 class="fw-bold text-gradient mb-0"><?php echo format_price($p['price']); ?></h4>
                                </div>
                                <h4 class="card-title fw-bold"><a href="product-detail.php?id=<?php echo $p['id']; ?>" class="text-reset"><?php echo htmlspecialchars($p['title']); ?></a></h4>
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars(substr($p['description'], 0, 110)) . '...'; ?>
                                </p>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <hr class="mb-3 border-color">
                            <div class="d-flex justify-content-between gap-2">
                                <a href="product-detail.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary rounded-pill w-100 py-2.5">Details</a>
                                <button class="btn btn-premium w-100 py-2.5" onclick="addToCart(<?php echo $p['id']; ?>, '<?php echo addslashes($p['title']); ?>', <?php echo $p['price']; ?>, '<?php echo $cover; ?>')">
                                    <i class="fas fa-shopping-cart me-1"></i> Buy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted">No featured products available at the moment. Check back soon!</div>
        <?php endif; ?>
    </div>
</div>

<hr class="my-5 opacity-10">

<!-- 3. PREMIUM ADVANTAGE & SECURITY (Dark/Light card grid) -->
<div class="py-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-5 text-center text-lg-start mb-4">
            <span class="text-gradient fw-bold small text-uppercase tracking-widest"><i class="fas fa-shield-halved me-2"></i>Security Focus</span>
            <h2 class="display-6 fw-bold mt-2">Engineered for absolute trust.</h2>
            <p class="text-muted mt-3">We safeguard your assets, transactions, and data behind state of the art structures. Direct download tokens expire dynamically to protect intellectual ownership.</p>
            <a href="products.php" class="btn btn-premium py-3 px-4 mt-3">Start Browsing</a>
        </div>
        <div class="col-lg-7">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card card-glass p-4 border-0 mb-4 h-100">
                        <div class="card-body">
                            <div class="bg-gradient-premium text-white p-3 rounded-4 d-inline-block mb-3">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-2">Secure Downloads</h4>
                            <p class="small text-muted mb-0">Files are served using single-use auto-expiring links that protect against hotlinking and piracy.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-glass p-4 border-0 mb-4 h-100">
                        <div class="card-body">
                            <div class="bg-gradient-premium text-white p-3 rounded-4 d-inline-block mb-3">
                                <i class="fas fa-credit-card fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-2">Simulated Gateway</h4>
                            <p class="small text-muted mb-0">Experience full Stripe, PayPal, and Razorpay simulations for 100% safe testing.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-5 opacity-10">

<!-- 3B. TESTIMONIALS SECTION -->
<div class="py-4">
    <div class="text-center mb-5">
        <span class="text-gradient fw-bold small text-uppercase tracking-widest"><i class="fas fa-star me-2"></i>Real Feedback</span>
        <h2 class="display-5 fw-bold mt-2">What Creators Are Saying</h2>
        <p class="text-muted col-md-6 mx-auto">Trusted by developers, designers, and startup founders worldwide.</p>
    </div>
    
    <div class="row g-4">
        <!-- Testimonial 1 -->
        <div class="col-md-4">
            <div class="card card-glass border-0 p-4 h-100">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <?php for ($s = 0; $s < 5; $s++): ?>
                            <i class="fas fa-star text-warning me-1" style="font-size: 13px;"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-muted small mb-4">"The SaaS boilerplate saved me weeks of setup. Clean PHP architecture, secure authentication, and fully working payment sandbox out of the box. Absolutely worth it."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-gradient-premium text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; flex-shrink: 0;">AK</div>
                        <div>
                            <h6 class="fw-bold mb-0">Aryan Kapoor</h6>
                            <span class="small text-muted">Full-Stack Developer, Bengaluru</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Testimonial 2 -->
        <div class="col-md-4">
            <div class="card card-glass border-0 p-4 h-100">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <?php for ($s = 0; $s < 5; $s++): ?>
                            <i class="fas fa-star text-warning me-1" style="font-size: 13px;"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-muted small mb-4">"The Figma UI Kit is breathtaking. Glassmorphism components, dark/light variants, responsive tokens — it's the premium design resource I've been searching for."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-gradient-premium text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; flex-shrink: 0;">SL</div>
                        <div>
                            <h6 class="fw-bold mb-0">Sophie Laurent</h6>
                            <span class="small text-muted">UI/UX Designer, Paris</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Testimonial 3 -->
        <div class="col-md-4">
            <div class="card card-glass border-0 p-4 h-100">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <?php for ($s = 0; $s < 5; $s++): ?>
                            <i class="fas fa-star text-warning me-1" style="font-size: 13px;"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-muted small mb-4">"Launched my e-commerce startup 3x faster using the Flutter source code. Material Design 3 transitions are buttery smooth. Secure downloads worked flawlessly."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-gradient-premium text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; flex-shrink: 0;">JO</div>
                        <div>
                            <h6 class="fw-bold mb-0">James O'Brien</h6>
                            <span class="small text-muted">Mobile App Founder, Dublin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-5 opacity-10">

<!-- 4. FREQUENTLY ASKED QUESTIONS SECTION -->
<div class="py-4">
    <div class="text-center mb-5">
        <span class="text-gradient fw-bold small text-uppercase tracking-widest"><i class="fas fa-circle-question me-2"></i>Have Questions?</span>
        <h2 class="display-5 fw-bold mt-2">Frequently Asked Questions</h2>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="accordion accordion-flush" id="faqAccordion">
                <?php if (!empty($faqs_list)): ?>
                    <?php foreach ($faqs_list as $i => $faq): ?>
                        <div class="accordion-item card-glass border-0 mb-3 overflow-hidden shadow-0" style="border: 1px solid var(--border-color) !important;">
                            <h2 class="accordion-header" id="heading_<?php echo $faq['id']; ?>">
                                <button class="accordion-button collapsed fw-bold text-reset shadow-0" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapse_<?php echo $faq['id']; ?>" aria-expanded="false" aria-controls="collapse_<?php echo $faq['id']; ?>" style="background: transparent; color: var(--text-color);">
                                    <i class="fas fa-question-circle me-2 text-primary"></i><?php echo htmlspecialchars($faq['question']); ?>
                                </button>
                            </h2>
                            <div id="collapse_<?php echo $faq['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading_<?php echo $faq['id']; ?>" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body text-muted small pt-0 pb-3 px-4">
                                    <?php echo htmlspecialchars($faq['answer']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted">No FAQs loaded yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
