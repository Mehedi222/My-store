<?php
// admin/settings.php - Super Admin Configurations
require_once 'includes/header.php';

// Strict Super Admin check (Editors are not allowed here!)
if (!has_role('admin')) {
    $_SESSION['alert_err'] = "You are not authorized to view the Configurations page.";
    header("Location: index.php");
    exit;
}

// Handle Form Saves
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        
        foreach ($_POST as $key => $val) {
            if ($key === 'save_settings') continue;
            
            $stmt->execute([$key, trim($val), trim($val)]);
        }
        
        $pdo->commit();
        $_SESSION['alert_success'] = "Global system settings updated successfully!";
        
        // Reload system configurations in current session
        header("Location: settings.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['alert_err'] = "Failed to update configurations: " . $e->getMessage();
    }
}
?>

<div class="row g-4 mb-4">
    <!-- Header Banner -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4">
            <h2 class="fw-bold mb-1"><i class="fas fa-cogs me-2 text-primary"></i>System &amp; API Configurations</h2>
            <p class="text-muted mb-0 small">Adjust system currency settings, Tax/VAT percentages, website branding texts, and payment gateway credentials.</p>
        </div>
    </div>
</div>

<form action="settings.php" method="POST">
    <div class="row g-4">
        <!-- Site Branding Box -->
        <div class="col-md-6 mb-4">
            <div class="card card-glass border-0 p-4 h-100">
                <h4 class="fw-bold mb-4 text-gradient"><i class="fas fa-cubes me-2"></i>Site Branding</h4>
                
                <div class="mb-3">
                    <label for="site_name" class="form-label fw-bold small text-muted">Website Name</label>
                    <input type="text" id="site_name" name="site_name" class="form-control form-control-premium" value="<?php echo htmlspecialchars(get_setting('site_name')); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="footer_text" class="form-label fw-bold small text-muted">Footer Copyright Text (HTML Entities allowed)</label>
                    <input type="text" id="footer_text" name="footer_text" class="form-control form-control-premium" value="<?php echo htmlspecialchars(get_setting('footer_text')); ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="currency" class="form-label fw-bold small text-muted">Base Currency</label>
                        <select id="currency" name="currency" class="form-select form-control-premium" required>
                            <option value="USD" <?php echo get_setting('currency') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                            <option value="INR" <?php echo get_setting('currency') === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                            <option value="EUR" <?php echo get_setting('currency') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                            <option value="GBP" <?php echo get_setting('currency') === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                        </select>
                    </div>
                    
                    <div class="col-6 mb-3">
                        <label for="tax_percent" class="form-label fw-bold small text-muted">VAT / GST Rate (%)</label>
                        <input type="number" step="0.01" id="tax_percent" name="tax_percent" class="form-control form-control-premium" value="<?php echo htmlspecialchars(get_setting('tax_percent')); ?>" required>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- API Credentials Box -->
        <div class="col-md-6 mb-4">
            <div class="card card-glass border-0 p-4 h-100">
                <h4 class="fw-bold mb-4 text-gradient"><i class="fas fa-credit-card me-2"></i>Payment Gateway Keys</h4>
                
                <!-- Stripe -->
                <h6 class="fw-bold text-primary mb-3"><i class="fab fa-stripe me-2"></i>Stripe Integration</h6>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="stripe_public_key" class="form-label small text-muted">Public Key</label>
                        <input type="text" id="stripe_public_key" name="stripe_public_key" class="form-control form-control-premium small" value="<?php echo htmlspecialchars(get_setting('stripe_public_key')); ?>">
                    </div>
                    <div class="col-6">
                        <label for="stripe_secret_key" class="form-label small text-muted">Secret Key</label>
                        <input type="password" id="stripe_secret_key" name="stripe_secret_key" class="form-control form-control-premium small" value="<?php echo htmlspecialchars(get_setting('stripe_secret_key')); ?>">
                    </div>
                </div>
                
                <hr class="border-color my-3">
                
                <!-- PayPal -->
                <h6 class="fw-bold text-info mb-3"><i class="fab fa-paypal me-2"></i>PayPal Express</h6>
                <div class="mb-3">
                    <label for="paypal_client_id" class="form-label small text-muted">Client ID</label>
                    <input type="text" id="paypal_client_id" name="paypal_client_id" class="form-control form-control-premium small" value="<?php echo htmlspecialchars(get_setting('paypal_client_id')); ?>">
                </div>
                
                <hr class="border-color my-3">
                
                <!-- Razorpay -->
                <h6 class="fw-bold text-success mb-3"><i class="fas fa-credit-card me-2"></i>Razorpay Gateway</h6>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="razorpay_key_id" class="form-label small text-muted">Key ID</label>
                        <input type="text" id="razorpay_key_id" name="razorpay_key_id" class="form-control form-control-premium small" value="<?php echo htmlspecialchars(get_setting('razorpay_key_id')); ?>">
                    </div>
                    <div class="col-6">
                        <label for="razorpay_key_secret" class="form-label small text-muted">Key Secret</label>
                        <input type="password" id="razorpay_key_secret" name="razorpay_key_secret" class="form-control form-control-premium small" value="<?php echo htmlspecialchars(get_setting('razorpay_key_secret')); ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Save button footer -->
        <div class="col-12 text-end mb-4">
            <button type="submit" name="save_settings" class="btn btn-premium btn-lg py-3 px-5"><i class="fas fa-save me-2"></i>Save Configuration Changes</button>
        </div>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>
