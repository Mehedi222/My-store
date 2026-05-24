<?php
// checkout.php - Checkout & Payment Gateway Simulator
// Load config first for all pre-output PHP processing
require_once 'config.php';

// Force authentication (must happen before any output)
require_auth();

// Verify items in cart
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart_items)) {
    $_SESSION['alert_err'] = "Your cart is empty. Please add items before checking out.";
    header("Location: products.php");
    exit;
}

// System Tax configurations
$tax_percent = floatval(get_setting('tax_percent', '18.00'));
$currency = get_setting('currency', 'USD');

// Calculation values
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += floatval($item['price']);
}

// Process coupon code if submitted via POST
$coupon_code = '';
$discount_amount = 0;
$coupon_error = '';
$coupon_success = '';

if (isset($_POST['apply_coupon'])) {
    $coupon_code = trim($_POST['coupon_code']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active'");
        $stmt->execute([$coupon_code]);
        $coupon = $stmt->fetch();
        
        if ($coupon) {
            $today = date('Y-m-d');
            if ($today > $coupon['expiry_date']) {
                $coupon_error = "This coupon code has expired.";
                $coupon_code = '';
            } elseif ($coupon['used_count'] >= $coupon['usage_limit']) {
                $coupon_error = "This coupon code has reached its usage limit.";
                $coupon_code = '';
            } else {
                // Apply coupon
                if ($coupon['type'] === 'percentage') {
                    $discount_amount = $subtotal * ($coupon['value'] / 100);
                } else {
                    $discount_amount = floatval($coupon['value']);
                }
                
                // Cap discount at subtotal
                if ($discount_amount > $subtotal) {
                    $discount_amount = $subtotal;
                }
                $coupon_success = "Coupon applied! Discount: " . format_price($discount_amount);
            }
        } else {
            $coupon_error = "Invalid coupon code.";
            $coupon_code = '';
        }
    } catch (PDOException $e) {
        $coupon_error = "Database error verifying coupon: " . $e->getMessage();
    }
}

// Retrieve values from persistent inputs
$coupon_code = isset($_POST['coupon_code_persistent']) ? $_POST['coupon_code_persistent'] : $coupon_code;
$discount_amount = isset($_POST['discount_amount_persistent']) ? floatval($_POST['discount_amount_persistent']) : $discount_amount;

// Final calculations
$tax_amount = ($subtotal - $discount_amount) * ($tax_percent / 100);
$final_total = $subtotal - $discount_amount + $tax_amount;

// Process Simulated Transaction Callback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulate_payment_success'])) {
    $payment_gateway = $_POST['payment_gateway'];
    $transaction_id = 'TXN_' . strtoupper(bin2hex(random_bytes(8)));
    $user = get_logged_in_user();
    
    try {
        $pdo->beginTransaction();
        
        // 1. Insert Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, coupon_code, discount_amount, tax_amount, tax_percent, final_amount, payment_gateway, transaction_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')");
        $stmt->execute([
            $user['id'],
            $subtotal,
            empty($coupon_code) ? null : $coupon_code,
            $discount_amount,
            $tax_amount,
            $tax_percent,
            $final_total,
            $payment_gateway,
            $transaction_id,
        ]);
        $order_id = $pdo->lastInsertId();
        
        // 2. Insert Order Items
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price) VALUES (?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt_item->execute([$order_id, $item['id'], $item['price']]);
        }
        
        // 3. Update Coupon Count if used
        if (!empty($coupon_code)) {
            $stmt_coupon = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
            $stmt_coupon->execute([$coupon_code]);
        }
        
        $pdo->commit();
        
        // Clear Cart session
        $_SESSION['cart'] = [];
        
        // Return a beautiful success page with a script that clears client-side cart
        echo '<!DOCTYPE html>
        <html>
        <head>
            <script>
                // Clear localStorage cart
                localStorage.removeItem("yst_cart");
                window.location.href = "orders.php?success=1";
            </script>
        </head>
        <body>
            <p>Processing payment transaction...</p>
        </body>
        </html>';
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['alert_err'] = "Transaction execution failed: " . $e->getMessage();
    }
}

// All pre-output logic done — now load the HTML header/layout
require_once 'includes/header.php';
?>

<div class="row g-4 mb-4">
    <!-- Header Banner -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4 mb-2">
            <h2 class="fw-bold mb-1"><i class="fas fa-credit-card me-2 text-primary"></i>Checkout Order Securely</h2>
            <p class="text-muted mb-0 small">Enter billing information and complete transaction safely.</p>
        </div>
    </div>
    
    <!-- Left Column - Billing and simulated gateways selector -->
    <div class="col-lg-7">
        <div class="card card-glass border-0 p-4 mb-4">
            <h4 class="fw-bold mb-4"><i class="fas fa-wallet me-2 text-primary"></i>Payment Method</h4>
            
            <form action="checkout.php" method="POST" id="checkout_form">
                <!-- Hidden inputs to persist coupon math -->
                <input type="hidden" name="coupon_code_persistent" value="<?php echo htmlspecialchars($coupon_code); ?>">
                <input type="hidden" name="discount_amount_persistent" value="<?php echo htmlspecialchars($discount_amount); ?>">
                
                <div class="mb-4">
                    <label for="payment_gateway" class="form-label fw-bold small text-muted"><i class="fas fa-shield-alt me-2 text-success"></i>Select Gateway</label>
                    <select id="payment_gateway" name="payment_gateway" class="form-select form-control-premium" required>
                        <option value="stripe">Stripe (Direct Card Transaction)</option>
                        <option value="paypal">PayPal Express Checkout</option>
                        <option value="razorpay">Razorpay (UPI / NetBanking / Cards)</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted"><i class="fas fa-info-circle me-2 text-primary"></i>Mock billing details</label>
                    <div class="p-3 border border-color rounded-3 bg-light text-muted small">
                        Testing digital downloads does not charge real currency. When you submit this order, a secure simulated gateway sandbox overlay will open.
                    </div>
                </div>
                
                <button type="button" class="btn btn-premium btn-lg w-100 py-3 mt-2" onclick="triggerGatewayModal()">Pay <?php echo format_price($final_total); ?></button>
            </form>
        </div>
    </div>
    
    <!-- Right Column - Order Breakdowns & Coupons -->
    <div class="col-lg-5">
        <!-- Coupon Validator -->
        <div class="card card-glass border-0 p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-ticket-alt me-2 text-primary"></i>Promo Code</h5>
            
            <?php if (!empty($coupon_error)): ?>
                <div class="alert alert-danger py-2 px-3 small border-0 mb-3"><i class="fas fa-exclamation-circle me-1"></i><?php echo $coupon_error; ?></div>
            <?php endif; ?>
            <?php if (!empty($coupon_success)): ?>
                <div class="alert alert-success py-2 px-3 small border-0 mb-3"><i class="fas fa-check-circle me-1"></i><?php echo $coupon_success; ?></div>
            <?php endif; ?>
            
            <form action="checkout.php" method="POST" class="d-flex gap-2">
                <input type="text" name="coupon_code" class="form-control form-control-premium" placeholder="e.g. WELCOME10" value="<?php echo htmlspecialchars($coupon_code); ?>">
                <button type="submit" name="apply_coupon" class="btn btn-primary rounded-pill px-4">Apply</button>
            </form>
        </div>
        
        <!-- Summary Math Box -->
        <div class="card card-glass border-0 p-4">
            <h4 class="fw-bold mb-4">Order Summary</h4>
            
            <!-- Items loop -->
            <div class="mb-3 border-bottom border-color pb-3 max-height-300 overflow-y-auto">
                <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span class="text-truncate" style="max-width: 70%;"><?php echo htmlspecialchars($item['title']); ?></span>
                        <span class="fw-bold"><?php echo format_price($item['price']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="d-flex justify-content-between mb-2 small text-muted">
                <span>Subtotal</span>
                <span><?php echo format_price($subtotal); ?></span>
            </div>
            
            <?php if ($discount_amount > 0): ?>
                <div class="d-flex justify-content-between mb-2 small text-success fw-bold">
                    <span>Discount (Coupon)</span>
                    <span>-<?php echo format_price($discount_amount); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between mb-3 small text-muted border-bottom border-color pb-2">
                <span>Estimated Tax (<?php echo $tax_percent; ?>%)</span>
                <span><?php echo format_price($tax_amount); ?></span>
            </div>
            
            <div class="d-flex justify-content-between mb-0">
                <h5 class="fw-bold mb-0">Total Due</h5>
                <h5 class="fw-bold text-gradient mb-0"><?php echo format_price($final_total); ?></h5>
            </div>
        </div>
    </div>
</div>

<!-- Beautiful, Simulated Payment Gateway Interactive Modal Overlay -->
<div class="modal fade" id="gatewayModal" tabindex="-1" aria-labelledby="gatewayModalLabel" aria-hidden="true" data-mdb-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-glass text-white border-0">
            <div class="modal-header border-0 pb-0 justify-content-between">
                <h5 class="modal-title fw-bold" id="gatewayModalLabel"><i class="fas fa-lock me-2 text-success"></i>Secure Gateway Sandbox</h5>
                <button type="button" class="btn-close btn-close-white" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 text-center text-dark" id="gateway_modal_body">
                <!-- Content injected dynamically via JS -->
            </div>
        </div>
    </div>
</div>

<script>
function triggerGatewayModal() {
    const gateway = document.getElementById('payment_gateway').value;
    const body = document.getElementById('gateway_modal_body');
    const modalEl = document.getElementById('gatewayModal');
    const modal = new mdb.Modal(modalEl);
    
    let gatewayHtml = '';
    const finalAmountStr = '<?php echo format_price($final_total); ?>';
    
    if (gateway === 'stripe') {
        gatewayHtml = `
            <div class="p-2 text-center text-dark">
                <i class="fab fa-stripe fa-4x text-primary mb-3"></i>
                <h4 class="fw-bold mb-1">Stripe Checkout</h4>
                <p class="text-muted small">Enter credit card details below to pay <strong>${finalAmountStr}</strong></p>
                
                <div class="text-start mt-4 mb-3">
                    <label class="small fw-bold text-muted mb-1"><i class="fas fa-credit-card me-1"></i>Card Number</label>
                    <input type="text" class="form-control form-control-premium mb-3" placeholder="4242 4242 4242 4242" value="4242 4242 4242 4242" disabled>
                    
                    <div class="row">
                        <div class="col-6">
                            <label class="small fw-bold text-muted mb-1">Expiry</label>
                            <input type="text" class="form-control form-control-premium" placeholder="12/28" value="12/28" disabled>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold text-muted mb-1">CVC</label>
                            <input type="text" class="form-control form-control-premium" placeholder="123" value="123" disabled>
                        </div>
                    </div>
                </div>
                
                <hr class="border-color">
                <button class="btn btn-premium w-100 py-3 mt-2" onclick="submitCompletedPayment('stripe')">Simulate Stripe Payment Success</button>
            </div>
        `;
    } else if (gateway === 'paypal') {
        gatewayHtml = `
            <div class="p-2 text-center text-dark">
                <i class="fab fa-paypal fa-4x text-info mb-3"></i>
                <h4 class="fw-bold mb-1">PayPal Express</h4>
                <p class="text-muted small">Authorize checkout sandbox transaction for <strong>${finalAmountStr}</strong></p>
                
                <div class="p-3 border border-color rounded-3 text-start small text-muted my-4 bg-light">
                    <i class="fas fa-info-circle me-1 text-primary"></i> Connected customer accounts will authorize funds for instant digital delivery clearance.
                </div>
                
                <hr class="border-color">
                <button class="btn btn-warning w-100 py-3 text-dark fw-bold mt-2" onclick="submitCompletedPayment('paypal')">Simulate PayPal Payment Success</button>
            </div>
        `;
    } else if (gateway === 'razorpay') {
        gatewayHtml = `
            <div class="p-2 text-center text-dark">
                <i class="fas fa-credit-card fa-4x text-success mb-3"></i>
                <h4 class="fw-bold mb-1">Razorpay Payment Gateway</h4>
                <p class="text-muted small">Authorize UPI / NetBanking transaction for <strong>${finalAmountStr}</strong></p>
                
                <div class="p-3 border border-color rounded-3 text-start small text-muted my-4 bg-light">
                    <i class="fas fa-qrcode me-2 text-success"></i> UPI ID: <strong class="text-dark">ystdigital@upi</strong>
                </div>
                
                <hr class="border-color">
                <button class="btn btn-primary w-100 py-3 mt-2 rounded-pill" onclick="submitCompletedPayment('razorpay')">Simulate Razorpay Payment Success</button>
            </div>
        `;
    }
    
    body.innerHTML = gatewayHtml;
    modal.show();
}

function submitCompletedPayment(gateway) {
    const form = document.getElementById('checkout_form');
    
    // Inject required inputs
    const successInput = document.createElement('input');
    successInput.type = 'hidden';
    successInput.name = 'simulate_payment_success';
    successInput.value = '1';
    form.appendChild(successInput);
    
    // Set actual selected gateway in form
    document.getElementById('payment_gateway').value = gateway;
    
    form.submit();
}
</script>

<?php require_once 'includes/footer.php'; ?>
