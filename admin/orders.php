<?php
// admin/orders.php - Admin Orders & Tax Tracking
require_once 'includes/header.php';

// Handle Refund processing
if (isset($_GET['refund'])) {
    $order_id = intval($_GET['refund']);
    try {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'refunded' WHERE id = ?");
        $stmt->execute([$order_id]);
        $_SESSION['alert_success'] = "Order #$order_id has been successfully refunded. Access revoked.";
        header("Location: orders.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert_err'] = "Refund action failed: " . $e->getMessage();
    }
}

// Retrieve orders
$orders = [];
try {
    $orders = $pdo->query("
        SELECT o.*, u.name as buyer_name, u.email as buyer_email 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.id DESC
    ")->fetchAll();
} catch (PDOException $e) {
    // Fail silent
}
?>

<div class="row g-4 mb-4">
    <!-- Header Banner -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4">
            <h2 class="fw-bold mb-1"><i class="fas fa-box-open me-2 text-primary"></i>Track Orders &amp; Revenue</h2>
            <p class="text-muted mb-0 small">Monitor transactional statuses, process refunds, and track VAT/GST collection summary.</p>
        </div>
    </div>
    
    <!-- Orders Listing Table -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4">
            <h4 class="fw-bold mb-4">Transactional Log</h4>
            
            <?php if (!empty($orders)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase border-bottom border-color">
                                <th>Order ID</th>
                                <th>Buyer Details</th>
                                <th>Financial Breakdown</th>
                                <th>Gateway &amp; Ref</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $ord): 
                                $status_badge = $ord['payment_status'] === 'completed' ? 'bg-success' : ($ord['payment_status'] === 'refunded' ? 'bg-warning text-dark' : 'bg-danger');
                                
                                // Fetch order items
                                $items = [];
                                try {
                                    $stmt_items = $pdo->prepare("
                                        SELECT oi.*, p.title 
                                        FROM order_items oi
                                        JOIN products p ON oi.product_id = p.id
                                        WHERE oi.order_id = ?
                                    ");
                                    $stmt_items->execute([$ord['id']]);
                                    $items = $stmt_items->fetchAll();
                                } catch (PDOException $e) {
                                    // Silent fail
                                }
                            ?>
                                <tr class="border-bottom border-color">
                                    <td class="small fw-bold">#<?php echo $ord['id']; ?></td>
                                    <td>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($ord['buyer_name']); ?></h6>
                                        <span class="small text-muted"><?php echo htmlspecialchars($ord['buyer_email']); ?></span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <strong>Total Paid:</strong> <span class="text-gradient fw-bold"><?php echo format_price($ord['final_amount']); ?></span> <br>
                                            <span class="text-muted">Tax: <?php echo format_price($ord['tax_amount']); ?> (<?php echo $ord['tax_percent']; ?>%)</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            <strong>Gateway:</strong> <?php echo htmlspecialchars(ucfirst($ord['payment_gateway'])); ?> <br>
                                            <strong>Ref:</strong> <?php echo htmlspecialchars($ord['transaction_id']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $status_badge; ?> px-2.5 py-1 rounded shadow-0 small"><?php echo strtoupper($ord['payment_status']); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <!-- Order Items details Dropdown menu -->
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-outline-primary btn-sm rounded-pill p-2 dropdown-toggle shadow-0" type="button" data-mdb-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end p-3 shadow-lg" style="width: 250px;">
                                                <h6 class="fw-bold mb-2">Items Purchased:</h6>
                                                <?php foreach ($items as $item): ?>
                                                    <li class="small text-muted mb-1 border-bottom border-color pb-1">
                                                        <i class="fas fa-cubes me-1"></i><?php echo htmlspecialchars($item['title']); ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        
                                        <!-- Invoice pdf -->
                                        <a href="../download.php?invoice=<?php echo $ord['id']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill p-2" target="_blank"><i class="fas fa-file-invoice"></i></a>
                                        
                                        <!-- Refund action trigger -->
                                        <?php if ($ord['payment_status'] === 'completed'): ?>
                                            <a href="orders.php?refund=<?php echo $ord['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1.5 small text-uppercase fw-bold" onclick="return confirm('Are you sure you want to refund order #<?php echo $ord['id']; ?>? This revokes downloads immediately.');">
                                                Refund
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted text-center py-5">No order records registered yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
