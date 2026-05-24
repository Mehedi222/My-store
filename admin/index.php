<?php
// admin/index.php - Core Admin Dashboard Analytics
require_once 'includes/header.php';

// Aggregating Metrics from database
$total_revenue = 0;
$tax_collected = 0;
$sales_count = 0;
$active_products = 0;
$open_tickets = 0;

try {
    // 1. Revenue & Sales
    $stmt = $pdo->query("SELECT COUNT(id) as cnt, SUM(final_amount) as rev, SUM(tax_amount) as tax FROM orders WHERE payment_status = 'completed'");
    $stats = $stmt->fetch();
    $sales_count = intval($stats['cnt']);
    $total_revenue = floatval($stats['rev']);
    $tax_collected = floatval($stats['tax']);
    
    // 2. Active Products count
    $stmt_prod = $pdo->query("SELECT COUNT(id) FROM products WHERE status = 'active'");
    $active_products = intval($stmt_prod->fetchColumn());
    
    // 3. Open support tickets count
    $stmt_tickets = $pdo->query("SELECT COUNT(id) FROM tickets WHERE status = 'open'");
    $open_tickets = intval($stmt_tickets->fetchColumn());
    
} catch (PDOException $e) {
    $_SESSION['alert_err'] = "Analytics aggregation failed: " . $e->getMessage();
}

// Fetch Top Selling Products joined with order items
$top_sellers = [];
try {
    $stmt_top = $pdo->query("
        SELECT p.title, p.category, p.price, COUNT(oi.id) as sales_volume 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.payment_status = 'completed'
        GROUP BY oi.product_id
        ORDER BY sales_volume DESC
        LIMIT 5
    ");
    $top_sellers = $stmt_top->fetchAll();
} catch (PDOException $e) {
    // Fail silent
}

// Fetch 5 Recent Transactions
$recent_orders = [];
try {
    $stmt_recent = $pdo->query("
        SELECT o.*, u.name as buyer_name 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.id DESC
        LIMIT 5
    ");
    $recent_orders = $stmt_recent->fetchAll();
} catch (PDOException $e) {
    // Fail silent
}
?>

<div class="row g-4 mb-4">
    <!-- Dashboard Header -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4 mb-2">
            <h2 class="fw-bold mb-1">Administrative Overview</h2>
            <p class="text-muted mb-0 small">Welcome back, <strong><?php echo htmlspecialchars($user['name']); ?></strong>. Track real-time digital sales, revenue, and support tickets.</p>
        </div>
    </div>
    
    <!-- Stat Card 1: Total Revenue -->
    <div class="col-md-3">
        <div class="card card-glass border-0 p-3 h-100">
            <div class="card-body text-center">
                <i class="fas fa-wallet fa-2x text-primary mb-3"></i>
                <h6 class="text-muted text-uppercase fw-bold small mb-1">Gross Revenue</h6>
                <h3 class="fw-bold text-gradient mb-0"><?php echo format_price($total_revenue); ?></h3>
                <span class="small text-success mt-1 d-inline-block"><i class="fas fa-caret-up me-1"></i><?php echo $sales_count; ?> Orders Completed</span>
            </div>
        </div>
    </div>
    
    <!-- Stat Card 2: Taxes Collected -->
    <div class="col-md-3">
        <div class="card card-glass border-0 p-3 h-100">
            <div class="card-body text-center">
                <i class="fas fa-percentage fa-2x text-success mb-3"></i>
                <h6 class="text-muted text-uppercase fw-bold small mb-1">Taxes Collected</h6>
                <h3 class="fw-bold text-gradient mb-0"><?php echo format_price($tax_collected); ?></h3>
                <span class="small text-muted mt-1 d-inline-block">VAT / GST Aggregate</span>
            </div>
        </div>
    </div>
    
    <!-- Stat Card 3: Active Products -->
    <div class="col-md-3">
        <div class="card card-glass border-0 p-3 h-100">
            <div class="card-body text-center">
                <i class="fas fa-cubes fa-2x text-warning mb-3"></i>
                <h6 class="text-muted text-uppercase fw-bold small mb-1">Active Products</h6>
                <h3 class="fw-bold text-gradient mb-0"><?php echo $active_products; ?></h3>
                <span class="small text-muted mt-1 d-inline-block">Digital Assets Catalog</span>
            </div>
        </div>
    </div>
    
    <!-- Stat Card 4: Open Tickets -->
    <div class="col-md-3">
        <div class="card card-glass border-0 p-3 h-100">
            <div class="card-body text-center">
                <i class="fas fa-headset fa-2x text-danger mb-3"></i>
                <h6 class="text-muted text-uppercase fw-bold small mb-1">Open Tickets</h6>
                <h3 class="fw-bold text-gradient mb-0"><?php echo $open_tickets; ?></h3>
                <span class="small text-danger mt-1 d-inline-block">Pending Customer Support</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Recent Transactions Table -->
    <div class="col-lg-7 mb-4">
        <div class="card card-glass border-0 p-4 h-100">
            <h4 class="fw-bold mb-4"><i class="fas fa-history me-2 text-primary"></i>Recent Transactions</h4>
            
            <?php if (!empty($recent_orders)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase border-bottom border-color">
                                <th>Order</th>
                                <th>Buyer</th>
                                <th>Gateway</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $ord): 
                                $status_badge = $ord['payment_status'] === 'completed' ? 'bg-success' : ($ord['payment_status'] === 'refunded' ? 'bg-warning text-dark' : 'bg-danger');
                            ?>
                                <tr class="border-bottom border-color">
                                    <td class="small fw-bold">#<?php echo $ord['id']; ?></td>
                                    <td class="small"><?php echo htmlspecialchars($ord['buyer_name']); ?></td>
                                    <td class="small text-muted"><?php echo htmlspecialchars(ucfirst($ord['payment_gateway'])); ?></td>
                                    <td class="small fw-bold"><?php echo format_price($ord['final_amount']); ?></td>
                                    <td><span class="badge <?php echo $status_badge; ?> px-2 py-0.5 rounded shadow-0 small"><?php echo strtoupper($ord['payment_status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted text-center py-5">No transaction records logged yet.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Right Column: Top Selling Products list -->
    <div class="col-lg-5 mb-4">
        <div class="card card-glass border-0 p-4 h-100">
            <h4 class="fw-bold mb-4"><i class="fas fa-trophy me-2 text-primary"></i>Top Selling Products</h4>
            
            <?php if (!empty($top_sellers)): ?>
                <div class="list-group list-group-light shadow-0">
                    <?php foreach ($top_sellers as $idx => $prod): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom border-color py-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">#<?php echo $idx+1; ?></span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-truncate" style="max-width: 220px;"><?php echo htmlspecialchars($prod['title']); ?></h6>
                                    <span class="small text-muted"><?php echo htmlspecialchars($prod['category']); ?></span>
                                </div>
                            </div>
                            <div class="text-end">
                                <h6 class="fw-bold mb-0"><?php echo format_price($prod['price']); ?></h6>
                                <span class="small text-success fw-bold"><?php echo $prod['sales_volume']; ?> Sales</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-muted text-center py-5">Complete orders to see top-selling stats.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
