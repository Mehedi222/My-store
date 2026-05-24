<?php
// admin/products.php - Admin Product CRUD Manager
require_once 'includes/header.php';

// Handle Delete Product action
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['alert_success'] = "Product deleted successfully!";
        header("Location: products.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert_err'] = "Failed to delete product: " . $e->getMessage();
    }
}

// Handle Add / Edit Product forms
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $file_path = trim($_POST['file_path']);
    $status = $_POST['status'];
    
    // Default screenshots cover selection
    $cover_selection = $_POST['cover_selection'];
    $screenshots_json = json_encode([$cover_selection]);
    
    if (empty($title) || empty($category) || empty($description) || empty($file_path)) {
        $_SESSION['alert_err'] = "All product fields are required.";
    } else {
        try {
            if ($id > 0) {
                // UPDATE Product
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET title = ?, category = ?, price = ?, description = ?, screenshots = ?, file_path = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $category, $price, $description, $screenshots_json, $file_path, $status, $id]);
                $_SESSION['alert_success'] = "Product details updated successfully!";
            } else {
                // INSERT Product
                $stmt = $pdo->prepare("
                    INSERT INTO products (title, category, price, description, screenshots, file_path, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $category, $price, $description, $screenshots_json, $file_path, $status]);
                $_SESSION['alert_success'] = "New digital product cataloged successfully!";
            }
            header("Location: products.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['alert_err'] = "Database write failure: " . $e->getMessage();
        }
    }
}

// Retrieve current products
$products = [];
try {
    $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    // Fail silent
}

// If edit is requested, fetch product details
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    foreach ($products as $p) {
        if ($p['id'] == $edit_id) {
            $edit_product = $p;
            break;
        }
    }
}
?>

<div class="row g-4">
    <!-- List of Products Grid -->
    <div class="col-lg-7 mb-4">
        <div class="card card-glass border-0 p-4">
            <h4 class="fw-bold mb-4"><i class="fas fa-cubes me-2 text-primary"></i>Manage Products</h4>
            
            <?php if (!empty($products)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase border-bottom border-color">
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): 
                                $status_color = $p['status'] === 'active' ? 'bg-success' : 'bg-secondary';
                            ?>
                                <tr class="border-bottom border-color">
                                    <td>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($p['title']); ?></h6>
                                        <span class="small text-muted text-truncate d-inline-block" style="max-width: 180px;"><?php echo htmlspecialchars($p['file_path']); ?></span>
                                    </td>
                                    <td class="small"><?php echo htmlspecialchars($p['category']); ?></td>
                                    <td class="small fw-bold"><?php echo format_price($p['price']); ?></td>
                                    <td><span class="badge <?php echo $status_color; ?> px-2 py-0.5 rounded shadow-0 small"><?php echo strtoupper($p['status']); ?></span></td>
                                    <td class="text-end">
                                        <a href="products.php?edit=<?php echo $p['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill p-2 hover-scale"><i class="fas fa-edit"></i></a>
                                        <a href="products.php?delete=<?php echo $p['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill p-2 hover-scale" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted text-center py-5">No digital products cataloged yet.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add / Edit Product Form Panel -->
    <div class="col-lg-5 mb-4">
        <div class="card card-glass border-0 p-4">
            <h4 class="fw-bold mb-4 text-gradient">
                <i class="fas fa-edit me-2"></i>
                <?php echo $edit_product ? 'Edit Product Details' : 'Add New Product'; ?>
            </h4>
            
            <form action="products.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $edit_product ? $edit_product['id'] : '0'; ?>">
                
                <!-- Title Input -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-bold small text-muted">Product Title</label>
                    <input type="text" id="title" name="title" class="form-control form-control-premium" value="<?php echo $edit_product ? htmlspecialchars($edit_product['title']) : ''; ?>" placeholder="e.g. Flutter UI E-Commerce App" required>
                </div>
                
                <div class="row">
                    <!-- Category Input -->
                    <div class="col-6 mb-3">
                        <label for="category" class="form-label fw-bold small text-muted">Category</label>
                        <select id="category" name="category" class="form-select form-control-premium" required>
                            <option value="Templates" <?php echo ($edit_product && $edit_product['category'] === 'Templates') ? 'selected' : ''; ?>>Templates</option>
                            <option value="Code" <?php echo ($edit_product && $edit_product['category'] === 'Code') ? 'selected' : ''; ?>>Code</option>
                            <option value="UI Kits" <?php echo ($edit_product && $edit_product['category'] === 'UI Kits') ? 'selected' : ''; ?>>UI Kits</option>
                            <option value="Mobile" <?php echo ($edit_product && $edit_product['category'] === 'Mobile') ? 'selected' : ''; ?>>Mobile</option>
                        </select>
                    </div>
                    
                    <!-- Price Input -->
                    <div class="col-6 mb-3">
                        <label for="price" class="form-label fw-bold small text-muted">Price (USD)</label>
                        <input type="number" step="0.01" id="price" name="price" class="form-control form-control-premium" value="<?php echo $edit_product ? htmlspecialchars($edit_product['price']) : '19.00'; ?>" required>
                    </div>
                </div>
                
                <!-- Secure File path Input -->
                <div class="mb-3">
                    <label for="file_path" class="form-label fw-bold small text-muted">Secure Zip File Path</label>
                    <input type="text" id="file_path" name="file_path" class="form-control form-control-premium" value="<?php echo $edit_product ? htmlspecialchars($edit_product['file_path']) : 'secure/new_product_asset.zip'; ?>" placeholder="secure/file.zip" required>
                </div>
                
                <!-- Visual Cover asset selection -->
                <div class="mb-3">
                    <label for="cover_selection" class="form-label fw-bold small text-muted">Product Cover Artwork</label>
                    <select id="cover_selection" name="cover_selection" class="form-select form-control-premium" required>
                        <option value="assets/images/dashboard_cover.webp">Premium Admin Dashboard Template</option>
                        <option value="assets/images/saas_boilerplate.webp">SaaS Boilerplate Cover</option>
                        <option value="assets/images/uikit_cover.webp">Glassmorphic Figma UI Kit</option>
                        <option value="assets/images/flutter_app_cover.webp">Mobile Flutter App UI</option>
                    </select>
                </div>
                
                <!-- Description Input -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-bold small text-muted">Description & Details</label>
                    <textarea id="description" name="description" class="form-control form-control-premium" rows="4" placeholder="Enter specifications..." required><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                </div>
                
                <!-- Status Toggle -->
                <div class="mb-4">
                    <label for="status" class="form-label fw-bold small text-muted">Product Visibility Status</label>
                    <select id="status" name="status" class="form-select form-control-premium" required>
                        <option value="active" <?php echo ($edit_product && $edit_product['status'] === 'active') ? 'selected' : ''; ?>>Active (Visible on Storefront)</option>
                        <option value="inactive" <?php echo ($edit_product && $edit_product['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive (Hidden)</option>
                    </select>
                </div>
                
                <button type="submit" name="save_product" class="btn btn-premium w-100 py-3"><i class="fas fa-save me-2"></i>Save Product Details</button>
                
                <?php if ($edit_product): ?>
                    <a href="products.php" class="btn btn-outline-primary rounded-pill w-100 py-2.5 mt-2">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
