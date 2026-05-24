<?php
// product-detail.php - Product Specification and Detailed View
require_once 'includes/header.php';

// Validate Product ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    $_SESSION['alert_err'] = "Database query error: " . $e->getMessage();
}

if (!$product) {
    $_SESSION['alert_err'] = "Requested product could not be found.";
    header("Location: products.php");
    exit;
}

// Fetch Related Products (same category, excluding current)
$related = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND status = 'active' LIMIT 3");
    $stmt->execute([$product['category'], $product['id']]);
    $related = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
}

// Parse screenshots JSON
$screenshots = json_decode($product['screenshots'], true);
if (empty($screenshots) || !is_array($screenshots)) {
    $screenshots = ['assets/images/placeholder.webp'];
}
$cover = $screenshots[0];
?>

<div class="row g-5 my-2">
    <!-- Breadcrumb -->
    <div class="col-12 mb-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars($product['category']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['title']); ?></li>
            </ol>
        </nav>
    </div>
    
    <!-- Left Column - Beautiful Carousel / Screenshots Slider -->
    <div class="col-lg-7">
        <div id="productCarousel" class="carousel slide card-glass p-1 border-0 overflow-hidden hover-scale" data-mdb-ride="carousel">
            <!-- Carousel indicators -->
            <div class="carousel-indicators">
                <?php foreach ($screenshots as $i => $shot): ?>
                    <button type="button" data-mdb-target="#productCarousel" data-mdb-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $i+1; ?>"></button>
                <?php endforeach; ?>
            </div>
            
            <!-- Carousel items -->
            <div class="carousel-inner" style="border-radius: 16px; height: 350px;">
                <?php foreach ($screenshots as $i => $shot): ?>
                    <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?> h-100">
                        <img src="<?php echo htmlspecialchars($shot); ?>" class="d-block w-100 h-100 object-fit-cover" alt="Product Screenshot <?php echo $i+1; ?>" />
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-mdb-target="#productCarousel" data-mdb-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-mdb-target="#productCarousel" data-mdb-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    
    <!-- Right Column - Specifications & Purchasing Panel -->
    <div class="col-lg-5">
        <div class="card card-glass border-0 p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-3"><?php echo htmlspecialchars($product['category']); ?></span>
                <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($product['title']); ?></h1>
                
                <div class="d-flex align-items-center gap-3 mb-4">
                    <h2 class="fw-bold text-gradient mb-0"><?php echo format_price($product['price']); ?></h2>
                    <span class="badge bg-success py-1.5 px-2.5 rounded shadow-0"><i class="fas fa-check me-1"></i>In Stock</span>
                </div>
                
                <hr class="border-color mb-4">
                
                <h5 class="fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>Description</h5>
                <p class="text-muted small leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>
            </div>
            
            <div>
                <hr class="border-color my-4">
                
                <!-- Action Controls -->
                <div class="d-flex gap-3">
                    <button class="btn btn-premium btn-lg w-100 py-3" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['title']); ?>', <?php echo $product['price']; ?>, '<?php echo $cover; ?>')">
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                </div>
                
                <!-- Security assurance badges -->
                <div class="d-flex justify-content-between mt-4 text-center">
                    <div class="small text-muted">
                        <i class="fas fa-shield-alt fa-lg text-success mb-2 d-block"></i> Secure Checkout
                    </div>
                    <div class="small text-muted">
                        <i class="fas fa-bolt fa-lg text-warning mb-2 d-block"></i> Instant Access
                    </div>
                    <div class="small text-muted">
                        <i class="fas fa-history fa-lg text-info mb-2 d-block"></i> Dynamic Tokens
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-5 opacity-10">

<!-- Related Products Listing Grid -->
<?php if (!empty($related)): ?>
    <div class="py-4">
        <h3 class="fw-bold mb-4 text-gradient"><i class="fas fa-cubes me-2"></i>Related Products</h3>
        <div class="row g-4">
            <?php foreach ($related as $rel): 
                $rel_shots = json_decode($rel['screenshots'], true);
                $rel_cover = (!empty($rel_shots) && is_array($rel_shots)) ? $rel_shots[0] : 'assets/images/placeholder.webp';
            ?>
                <div class="col-md-4">
                    <div class="card card-glass border-0 overflow-hidden h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="bg-image hover-overlay ripple" style="height: 160px; overflow: hidden;">
                                <img src="<?php echo htmlspecialchars($rel_cover); ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($rel['title']); ?>" />
                                <a href="product-detail.php?id=<?php echo $rel['id']; ?>">
                                    <div class="mask" style="background-color: rgba(251, 251, 251, 0.15);"></div>
                                </a>
                            </div>
                            <div class="card-body">
                                <h5 class="fw-bold"><a href="product-detail.php?id=<?php echo $rel['id']; ?>" class="text-reset"><?php echo htmlspecialchars($rel['title']); ?></a></h5>
                                <h5 class="fw-bold text-gradient mt-2"><?php echo format_price($rel['price']); ?></h5>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <hr class="mb-3 border-color">
                            <a href="product-detail.php?id=<?php echo $rel['id']; ?>" class="btn btn-outline-primary rounded-pill w-100">View Product</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
