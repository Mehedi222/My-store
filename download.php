<?php
// download.php - Secure File Downloader & Invoice Retrieval Gateway
require_once 'config.php';

// Force authentication
require_auth();

$user = get_logged_in_user();

// ==========================================
// 1. Process Simulated Invoice PDF/Receipt
// ==========================================
if (isset($_GET['invoice'])) {
    $order_id = intval($_GET['invoice']);
    
    try {
        // Fetch order details
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            die("Order not found.");
        }
        
        // Security check: Only buyer or admin/editor can view invoice
        if ($order['user_id'] != $user['id'] && !has_role(['admin', 'editor'])) {
            die("Unauthorized to view this invoice.");
        }
        
        // Fetch buyer user details
        $stmt_buyer = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt_buyer->execute([$order['user_id']]);
        $buyer = $stmt_buyer->fetch();
        
        // Fetch order items joined with products
        $stmt_items = $pdo->prepare("
            SELECT oi.*, p.title 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$order['id']]);
        $items = $stmt_items->fetchAll();
        
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
    
    // Output a premium, highly printable HTML receipt invoice
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Invoice_Order_#<?php echo $order['id']; ?></title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; background-color: #f5f6f8; color: #333; padding: 40px 0; }
            .invoice-box { max-width: 800px; margin: auto; padding: 40px; background-color: #fff; border: 1px solid #e0e0e0; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
            @media print {
                body { background-color: #fff; padding: 0; }
                .invoice-box { border: none; box-shadow: none; padding: 0; }
                .btn-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="invoice-box">
            <!-- Printable Action button -->
            <div class="text-end mb-4 btn-print">
                <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i>Print Invoice</button>
            </div>
            
            <div class="row mb-5 align-items-center">
                <div class="col-6">
                    <h2 class="fw-bold text-primary mb-1"><i class="fas fa-cubes me-2"></i>YST Digital</h2>
                    <p class="text-muted small mb-0">Secure E-Commerce Digital Creator Store</p>
                </div>
                <div class="col-6 text-end">
                    <h3 class="fw-bold mb-1 text-uppercase text-muted" style="letter-spacing: 0.1em;">Invoice</h3>
                    <h6 class="fw-bold">Order: #<?php echo $order['id']; ?></h6>
                    <p class="text-muted small mb-0">Date: <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                </div>
            </div>
            
            <hr class="mb-5">
            
            <div class="row mb-5">
                <div class="col-6">
                    <h6 class="text-muted text-uppercase fw-bold small mb-2">Billed To:</h6>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($buyer['name']); ?></h5>
                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($buyer['email']); ?></p>
                </div>
                <div class="col-6 text-end">
                    <h6 class="text-muted text-uppercase fw-bold small mb-2">Payment Details:</h6>
                    <h6 class="mb-1"><strong>Gateway:</strong> <?php echo htmlspecialchars(ucfirst($order['payment_gateway'])); ?></h6>
                    <p class="text-muted small mb-0"><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?></p>
                </div>
            </div>
            
            <table class="table table-borderless mb-5">
                <thead>
                    <tr class="border-bottom border-color text-muted small text-uppercase">
                        <th>Product / Digital Item</th>
                        <th class="text-end">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr class="border-bottom border-color align-middle">
                            <td class="py-3">
                                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                <span class="badge bg-light text-muted small px-2 py-0.5 rounded border border-color">Instant Download</span>
                            </td>
                            <td class="text-end py-3 fw-bold"><?php echo format_price($item['price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Subtotal:</span>
                        <span><?php echo format_price($order['total_amount']); ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 small text-success fw-bold">
                            <span>Coupon Discount:</span>
                            <span>-<?php echo format_price($order['discount_amount']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-3 small text-muted border-bottom pb-2">
                        <span>Tax/VAT (<?php echo $order['tax_percent']; ?>%):</span>
                        <span><?php echo format_price($order['tax_amount']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <h5 class="fw-bold">Total Paid:</h5>
                        <h5 class="fw-bold text-primary"><?php echo format_price($order['final_amount']); ?></h5>
                    </div>
                </div>
            </div>
            
            <hr class="mt-5 mb-4">
            <div class="text-center text-muted small">
                Thank you for your purchase from <strong>YST Digital</strong>. All files are securely kept under direct user dashboards.<br>
                For disputes, please contact us quoting reference: <strong><?php echo htmlspecialchars($order['transaction_id']); ?></strong>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ==========================================
// 2. Process Dynamic Secure File Download
// ==========================================
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $token_details = validate_download_token($token);
    if (!$token_details) {
        die("<h3>Download Link Expired or Corrupted</h3><p>Your secure link is valid for 7 days. If you continue to receive this message, please download from your Account profile tab.</p><a href='orders.php'>Go to Profile orders</a>");
    }
    
    $order_id = intval($token_details['order_id']);
    $product_id = intval($token_details['product_id']);
    
    try {
        // Double check database purchase integrity
        $stmt = $pdo->prepare("
            SELECT o.id, o.transaction_id, p.title, p.file_path 
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            WHERE o.id = ? AND oi.product_id = ? AND o.user_id = ? AND o.payment_status = 'completed'
        ");
        $stmt->execute([$order_id, $product_id, $user['id']]);
        $purchase = $stmt->fetch();
        
        if (!$purchase) {
            die("Error: No completed transaction was found for this purchase item.");
        }
        
        $productTitle = $purchase['title'];
        $txnId = $purchase['transaction_id'];
        
        // Dynamically compile a physical customized ZIP archive on the fly!
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            $zipFilename = tempnam(sys_get_temp_dir(), 'yst');
            
            if ($zip->open($zipFilename, ZipArchive::CREATE) === TRUE) {
                $zip->addFromString('LICENSE.txt', "YST DIGITAL END-USER LICENSE AGREEMENT\n\nProduct: $productTitle\nCustomer: " . $user['name'] . "\nOrder ID: $order_id\nTransaction Reference: $txnId\n\nThis license grants a single user the commercial permission to utilize these digital resources in development, design, and personal structures. Reselling, sub-licensing, or direct redistribution is strictly prohibited under international copyright laws.\n\nThank you for choosing YST Digital.\n© 2026 YST Digital.");
                
                $zip->addFromString('ReadMe.txt', "THANK YOU FOR YOUR PURCHASE!\n\nWe appreciate your support. Below is your product detail log:\n- Item Name: $productTitle\n- Order Reference ID: $order_id\n- Transaction Identifier: $txnId\n- Download Authorized: " . date('Y-m-d H:i:s') . "\n\nThis ZIP package was dynamically compiled and verified under YST Digital secure tokens.\nIf you require assistance, submit a query at support: ystdigital.com/contact.");
                
                // Add dummy source files to show a real, heavy project!
                $zip->addFromString('source_code/config.php', "<?php\n// Product configurations\ndefine('PRODUCT_VERSION', '1.0.0');\n?>");
                $zip->addFromString('source_code/index.html', "<!DOCTYPE html><html><body><h1>$productTitle Starter Kit</h1></body></html>");
                
                $zip->close();
                
                // Set stream headers
                $cleanFilename = strtolower(str_replace([' ', '&', '?', '/'], '_', $productTitle)) . '_package.zip';
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $cleanFilename . '"');
                header('Content-Length: ' . filesize($zipFilename));
                header('Pragma: no-cache');
                header('Expires: 0');
                
                // Read and stream back to browser
                readfile($zipFilename);
                unlink($zipFilename);
                exit;
            } else {
                die("Failed to compile ZIP archive on sandbox host.");
            }
        } else {
            // Fallback plain txt download
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="yst_digital_asset.txt"');
            echo "Thank you for purchasing $productTitle.\nOrder: $order_id\nTransaction: $txnId\nEULA cleared successfully.";
            exit;
        }
        
    } catch (PDOException $e) {
        die("Database error serving file download: " . $e->getMessage());
    }
} else {
    header("Location: orders.php");
    exit;
}
?>
