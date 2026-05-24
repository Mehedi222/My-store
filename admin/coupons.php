<?php
// admin/coupons.php - Admin Coupons & Discount Manager
require_once 'includes/header.php';

// Handle Delete Coupon action
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['alert_success'] = "Coupon code deleted successfully!";
        header("Location: coupons.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert_err'] = "Failed to delete coupon: " . $e->getMessage();
    }
}

// Handle Add Coupon forms
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type'];
    $value = floatval($_POST['value']);
    $expiry_date = $_POST['expiry_date'];
    $usage_limit = intval($_POST['usage_limit']);
    
    if (empty($code) || empty($expiry_date) || $value <= 0 || $usage_limit <= 0) {
        $_SESSION['alert_err'] = "All coupon fields must contain valid values.";
    } else {
        try {
            // Check if coupon exists
            $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
            $stmt->execute([$code]);
            if ($stmt->fetch()) {
                $_SESSION['alert_err'] = "Coupon code already exists in catalog.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO coupons (code, type, value, expiry_date, usage_limit, used_count, status) 
                    VALUES (?, ?, ?, ?, ?, 0, 'active')
                ");
                $stmt->execute([$code, $type, $value, $expiry_date, $usage_limit]);
                $_SESSION['alert_success'] = "Promo Code #$code cataloged successfully!";
                header("Location: coupons.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['alert_err'] = "Database write failure: " . $e->getMessage();
        }
    }
}

// Retrieve coupons
$coupons = [];
try {
    $coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    // Fail silent
}
?>

<div class="row g-4">
    <!-- List of Coupons Grid -->
    <div class="col-lg-7 mb-4">
        <div class="card card-glass border-0 p-4">
            <h4 class="fw-bold mb-4"><i class="fas fa-ticket-alt me-2 text-primary"></i>Active Coupon Codes</h4>
            
            <?php if (!empty($coupons)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase border-bottom border-color">
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Validity &amp; Expiry</th>
                                <th>Usage Tracker</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $c): 
                                $is_expired = date('Y-m-d') > $c['expiry_date'];
                                $status_badge = ($c['status'] === 'active' && !$is_expired) ? 'bg-success' : 'bg-secondary';
                                $discount_display = $c['type'] === 'percentage' ? intval($c['value']) . '%' : format_price($c['value']);
                            ?>
                                <tr class="border-bottom border-color">
                                    <td>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($c['code']); ?></h6>
                                        <span class="badge <?php echo $status_badge; ?> px-2 py-0.5 rounded shadow-0 small mt-1">
                                            <?php echo $is_expired ? 'EXPIRED' : strtoupper($c['status']); ?>
                                        </span>
                                    </td>
                                    <td class="small fw-bold text-success"><?php echo $discount_display; ?> OFF</td>
                                    <td>
                                        <div class="small text-muted">
                                            <strong>Expires:</strong> <?php echo date('Y-m-d', strtotime($c['expiry_date'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <strong>Used:</strong> <?php echo $c['used_count']; ?> / <?php echo $c['usage_limit']; ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a href="coupons.php?delete=<?php echo $c['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill p-2 hover-scale" onclick="return confirm('Are you sure you want to delete this coupon?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted text-center py-5">No coupon codes registered yet.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Coupon Form Panel -->
    <div class="col-lg-5 mb-4">
        <div class="card card-glass border-0 p-4">
            <h4 class="fw-bold mb-4 text-gradient"><i class="fas fa-plus me-2"></i>Create Promo Code</h4>
            
            <form action="coupons.php" method="POST">
                <!-- Code Input -->
                <div class="mb-3">
                    <label for="code" class="form-label fw-bold small text-muted">Coupon Code</label>
                    <input type="text" id="code" name="code" class="form-control form-control-premium" placeholder="e.g. FLASH25" style="text-transform: uppercase;" required>
                </div>
                
                <div class="row">
                    <!-- Type Input -->
                    <div class="col-6 mb-3">
                        <label for="type" class="form-label fw-bold small text-muted">Discount Type</label>
                        <select id="type" name="type" class="form-select form-control-premium" required>
                            <option value="percentage">Percentage (%)</option>
                            <option value="flat">Flat Amount (USD)</option>
                        </select>
                    </div>
                    
                    <!-- Value Input -->
                    <div class="col-6 mb-3">
                        <label for="value" class="form-label fw-bold small text-muted">Discount Value</label>
                        <input type="number" step="0.01" id="value" name="value" class="form-control form-control-premium" value="10" required>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Expiry Date -->
                    <div class="col-6 mb-3">
                        <label for="expiry_date" class="form-label fw-bold small text-muted">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-control form-control-premium" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                    </div>
                    
                    <!-- Limit Input -->
                    <div class="col-6 mb-3">
                        <label for="usage_limit" class="form-label fw-bold small text-muted">Usage Limit (Times)</label>
                        <input type="number" id="usage_limit" name="usage_limit" class="form-control form-control-premium" value="100" required>
                    </div>
                </div>
                
                <button type="submit" name="save_coupon" class="btn btn-premium w-100 py-3 mt-2"><i class="fas fa-save me-2"></i>Save Coupon Code</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
