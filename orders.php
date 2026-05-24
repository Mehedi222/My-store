<?php
// orders.php - User Order History and Secure Downloads Portal
require_once 'includes/header.php';

// Force authentication
require_auth();

$user = get_logged_in_user();

// Fetch successful or refunded orders for user
$orders = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['alert_err'] = "Database query failed: " . $e->getMessage();
}

// Redirect alert from checkout success
$just_ordered = isset($_GET['success']) && $_GET['success'] == 1;
?>

<!-- Success Confetti Card if just finished checkout -->
<?php if ($just_ordered): ?>
    <div class="card card-glass border-0 p-5 text-center mb-4 bg-gradient-premium text-white hover-scale">
        <div class="card-body">
            <i class="fas fa-check-circle fa-5x mb-3 text-white"></i>
            <h1 class="fw-bold mb-2">Payment Authorized!</h1>
            <p class="lead mb-4">Thank you for your purchase. Your digital assets are now cleared and ready to download securely below.</p>
            <a href="#downloads" class="btn btn-light rounded-pill px-4 py-2.5 text-primary fw-bold shadow-sm">View Download Links</a>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <!-- User Dashboard Sidebar Include -->
    <div class="col-md-4 mb-4">
        <div class="card card-glass border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-circle fa-5x text-gradient mb-3"></i>
                <h4 class="fw-bold text-gradient"><?php echo htmlspecialchars($user['name']); ?></h4>
                <span class="badge bg-primary px-3 py-2 rounded-pill"><?php echo strtoupper($user['role']); ?></span>
                <p class="text-muted small mt-2">Member account active</p>
                
                <hr class="my-4">
                
                <div class="list-group list-group-light text-start shadow-0">
                    <a href="profile.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-2">
                        <i class="fas fa-user-edit me-3"></i>Edit Profile
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action active px-3 border-0 rounded-3 mb-2">
                        <i class="fas fa-box-open me-3"></i>My Purchases
                    </a>
                    <a href="contact.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3">
                        <i class="fas fa-question-circle me-3"></i>Get Help
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Orders History Panel -->
    <div class="col-md-8" id="downloads">
        <div class="card card-glass border-0 p-4">
            <div class="card-body">
                <h3 class="fw-bold text-gradient mb-4">My Digital Assets & Orders</h3>
                
                <?php if (!empty($orders)): ?>
                    <div class="accordion accordion-flush" id="ordersAccordion">
                        <?php foreach ($orders as $index => $order): 
                            // Fetch Order Items joined with Products
                            $items = [];
                            try {
                                $stmt_items = $pdo->prepare("
                                    SELECT oi.*, p.title, p.category, p.screenshots 
                                    FROM order_items oi 
                                    JOIN products p ON oi.product_id = p.id 
                                    WHERE oi.order_id = ?
                                ");
                                $stmt_items->execute([$order['id']]);
                                $items = $stmt_items->fetchAll();
                            } catch (PDOException $e) {
                                // Silent fail
                            }
                            
                            $status_class = $order['payment_status'] === 'completed' ? 'bg-success' : ($order['payment_status'] === 'refunded' ? 'bg-warning text-dark' : 'bg-danger');
                        ?>
                            <!-- Individual Order Row Card -->
                            <div class="accordion-item card-glass border-0 mb-3 overflow-hidden shadow-0" style="border: 1px solid var(--border-color) !important;">
                                <h2 class="accordion-header" id="order_heading_<?php echo $order['id']; ?>">
                                    <button class="accordion-button collapsed fw-bold text-reset shadow-0" type="button" data-mdb-toggle="collapse" data-mdb-target="#order_collapse_<?php echo $order['id']; ?>" aria-expanded="false" aria-controls="order_collapse_<?php echo $order['id']; ?>" style="background: transparent; color: var(--text-color);">
                                        <div class="w-100 d-flex flex-wrap justify-content-between align-items-center me-3 g-2">
                                            <div>
                                                <span class="small text-muted block">Order ID: #<?php echo $order['id']; ?></span>
                                                <h6 class="fw-bold mb-0 mt-1"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></h6>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge <?php echo $status_class; ?> px-2.5 py-1 rounded shadow-0 mb-1 d-inline-block"><?php echo strtoupper($order['payment_status']); ?></span>
                                                <h6 class="fw-bold mb-0 text-gradient block"><?php echo format_price($order['final_amount']); ?></h6>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                
                                <div id="order_collapse_<?php echo $order['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="order_heading_<?php echo $order['id']; ?>" data-mdb-parent="#ordersAccordion">
                                    <div class="accordion-body border-top border-color pt-3 px-4">
                                        <!-- Transaction metadata details -->
                                        <div class="row g-2 mb-4 bg-light rounded p-3 text-muted small border border-color">
                                            <div class="col-sm-6"><strong>Gateway:</strong> <?php echo htmlspecialchars(ucfirst($order['payment_gateway'])); ?></div>
                                            <div class="col-sm-6"><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?></div>
                                            <div class="col-sm-6"><strong>Tax Paid:</strong> <?php echo format_price($order['tax_amount']); ?> (<?php echo $order['tax_percent']; ?>%)</div>
                                            <div class="col-sm-6">
                                                <!-- Simulated PDF invoice link -->
                                                <a href="download.php?invoice=<?php echo $order['id']; ?>" class="fw-bold text-primary"><i class="fas fa-file-invoice me-1"></i>Download PDF Invoice</a>
                                            </div>
                                        </div>
                                        
                                        <!-- Order Items list with dynamic expiry download buttons -->
                                        <h5 class="fw-bold mb-3"><i class="fas fa-cubes me-2 text-primary"></i>Items Included</h5>
                                        <div class="d-flex flex-column gap-3 mb-2">
                                            <?php foreach ($items as $item): 
                                                $screenshots = json_decode($item['screenshots'], true);
                                                $cover = (!empty($screenshots) && is_array($screenshots)) ? $screenshots[0] : 'assets/images/placeholder.webp';
                                                
                                                // Generate Dynamic Download Token
                                                $download_token = generate_download_token($order['id'], $item['product_id']);
                                            ?>
                                                <div class="p-3 border border-color rounded-3 bg-white" style="background: var(--card-bg) !important;">
                                                    <div class="row align-items-center g-3">
                                                        <div class="col-auto">
                                                            <img src="<?php echo htmlspecialchars($cover); ?>" class="rounded-3" style="width: 55px; height: 55px; object-fit: cover;" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                                        </div>
                                                        <div class="col">
                                                            <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                            <span class="badge bg-secondary py-0.5 px-2 rounded small"><?php echo htmlspecialchars($item['category']); ?></span>
                                                        </div>
                                                        <div class="col-auto">
                                                            <?php if ($order['payment_status'] === 'completed'): ?>
                                                                <a href="download.php?token=<?php echo urlencode($download_token); ?>" class="btn btn-premium btn-sm py-2 px-3 hover-scale">
                                                                    <i class="fas fa-cloud-download-alt me-1"></i>Secure Download
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-secondary btn-sm py-2 px-3" disabled>
                                                                    <i class="fas fa-lock me-1"></i> Locked
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3 text-primary"></i>
                        <h4 class="fw-bold">No Purchases Yet</h4>
                        <p class="small mb-0">Browse our store and complete a sandbox purchase to view downloads here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
