<?php
// products.php - Product Listing and Catalog Page
require_once 'config.php';

// Handle Client Cart Sync API endpoint
if (isset($_GET['action']) && $_GET['action'] === 'sync') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $_SESSION['cart'] = isset($data['cart']) ? $data['cart'] : [];
    echo json_encode(['status' => 'success', 'items_count' => count($_SESSION['cart'])]);
    exit;
}

require_once 'includes/header.php';

// Retrieve Filters & Sorting parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';

// Build SQL Query dynamically
$query = "SELECT * FROM products WHERE status = 'active'";
$params = [];

if ($search !== '') {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category !== '') {
    $query .= " AND category = ?";
    $params[] = $category;
}

// Handle sorting
if ($sort === 'price_low') {
    $query .= " ORDER BY price ASC";
} elseif ($sort === 'price_high') {
    $query .= " ORDER BY price DESC";
} elseif ($sort === 'popular') {
    $query .= " ORDER BY id ASC"; // Default popularity seed
} else {
    $query .= " ORDER BY id DESC"; // Default newest
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $_SESSION['alert_err'] = "Query failed: " . $e->getMessage();
}

// Fetch categories for filter dropdown
$categories = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE status = 'active'");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Fail silent
}
?>

<div class="row g-4 mb-4">
    <!-- Filters Header Banner -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4 mb-2">
            <h2 class="fw-bold mb-1">Browse Premium Digital Products</h2>
            <p class="text-muted mb-0 small">Find and download high-quality design kits, boilerplates, and source code bundles.</p>
        </div>
    </div>
    
    <!-- Left Column - Interactive Filters Panel -->
    <div class="col-lg-3">
        <div class="card card-glass border-0 p-4 sticky-lg-top" style="top: 90px; z-index: 10;">
            <h4 class="fw-bold mb-4"><i class="fas fa-sliders-h me-2 text-primary"></i>Filters</h4>
            
            <form action="products.php" method="GET">
                <!-- Search Filter -->
                <div class="mb-4">
                    <label for="search" class="form-label fw-bold small text-muted"><i class="fas fa-search me-2"></i>Search Title</label>
                    <input type="text" id="search" name="search" class="form-control form-control-premium" placeholder="Template, SaaS..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <!-- Category Filter -->
                <div class="mb-4">
                    <label for="category" class="form-label fw-bold small text-muted"><i class="fas fa-tags me-2"></i>Category</label>
                    <select id="category" name="category" class="form-select form-control-premium">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Sort Order Filter -->
                <div class="mb-4">
                    <label for="sort" class="form-label fw-bold small text-muted"><i class="fas fa-sort me-2"></i>Sort By</label>
                    <select id="sort" name="sort" class="form-select form-control-premium">
                        <option value="">Default (Newest)</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Popularity</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-premium w-100 py-3 mb-2"><i class="fas fa-filter me-2"></i>Apply Filters</button>
                <a href="products.php" class="btn btn-outline-primary rounded-pill w-100 py-2.5">Clear All</a>
            </form>
        </div>
    </div>
    
    <!-- Right Column - Dynamic Responsive Grid/List -->
    <div class="col-lg-9">
        <?php if (!empty($products)): ?>
            <!-- Desktop Layout Grid -->
            <div class="d-none d-md-grid product-grid">
                <?php foreach ($products as $p): 
                    $screenshots = json_decode($p['screenshots'], true);
                    $cover = (!empty($screenshots) && is_array($screenshots)) ? $screenshots[0] : 'assets/images/placeholder.webp';
                ?>
                    <div class="card card-glass border-0 overflow-hidden d-flex flex-column justify-content-between h-100">
                        <div>
                            <div class="bg-image hover-overlay ripple" style="height: 180px; overflow: hidden;">
                                <img src="<?php echo htmlspecialchars($cover); ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($p['title']); ?>" />
                                <a href="product-detail.php?id=<?php echo $p['id']; ?>">
                                    <div class="mask" style="background-color: rgba(251, 251, 251, 0.15);"></div>
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary px-2.5 py-1.5 rounded-pill"><?php echo htmlspecialchars($p['category']); ?></span>
                                    <h4 class="fw-bold text-gradient mb-0"><?php echo format_price($p['price']); ?></h4>
                                </div>
                                <h4 class="card-title fw-bold mb-2"><a href="product-detail.php?id=<?php echo $p['id']; ?>" class="text-reset"><?php echo htmlspecialchars($p['title']); ?></a></h4>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($p['description'], 0, 95)) . '...'; ?></p>
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
                <?php endforeach; ?>
            </div>
            
            <!-- Mobile Layout Vertical List (Native App Experience) -->
            <div class="d-md-none">
                <?php foreach ($products as $p): 
                    $screenshots = json_decode($p['screenshots'], true);
                    $cover = (!empty($screenshots) && is_array($screenshots)) ? $screenshots[0] : 'assets/images/placeholder.webp';
                ?>
                    <div class="product-mobile-card">
                        <img src="<?php echo htmlspecialchars($cover); ?>" class="product-mobile-image" alt="<?php echo htmlspecialchars($p['title']); ?>">
                        <div class="product-mobile-details">
                            <div>
                                <h5 class="fw-bold mb-1 text-truncate"><a href="product-detail.php?id=<?php echo $p['id']; ?>" class="text-reset"><?php echo htmlspecialchars($p['title']); ?></a></h5>
                                <span class="badge bg-primary px-2 py-1 rounded-pill mb-1"><?php echo htmlspecialchars($p['category']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <h5 class="fw-bold text-gradient mb-0"><?php echo format_price($p['price']); ?></h5>
                                <button class="btn btn-premium btn-sm py-1.5 px-3" onclick="addToCart(<?php echo $p['id']; ?>, '<?php echo addslashes($p['title']); ?>', <?php echo $p['price']; ?>, '<?php echo $cover; ?>')">
                                    <i class="fas fa-plus"></i> Buy
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card card-glass border-0 p-5 text-center text-muted">
                <i class="fas fa-folder-open fa-3x mb-3 text-primary"></i>
                <h4 class="fw-bold">No Products Found</h4>
                <p class="small mb-0">Try adjusting your filters or search query to find other assets.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
