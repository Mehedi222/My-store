<?php
// config.example.php
// ==========================================
// Copy this file to config.php and fill in
// your actual database credentials and keys.
// DO NOT commit config.php to version control.
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Database Credentials ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Your MySQL username
define('DB_PASS', '');              // Your MySQL password
define('DB_NAME', 'yst_digital_store');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Load System Settings dynamically
$sys_settings = [];
try {
    $stmt = $pdo->query("SELECT * FROM settings");
    while ($row = $stmt->fetch()) {
        $sys_settings[$row['key']] = $row['value'];
    }
} catch (PDOException $e) {
    // Table may not exist yet during install
}

function get_setting($key, $default = '') {
    global $sys_settings;
    return isset($sys_settings[$key]) ? $sys_settings[$key] : $default;
}

function format_price($price) {
    $currency = get_setting('currency', 'USD');
    $symbol = '$';
    if ($currency === 'INR') $symbol = '₹';
    elseif ($currency === 'EUR') $symbol = '€';
    elseif ($currency === 'GBP') $symbol = '£';
    return $symbol . number_format($price, 2);
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_logged_in_user() {
    if (!is_logged_in()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role']
    ];
}

function has_role($roles) {
    if (!is_logged_in()) return false;
    if (is_string($roles)) return $_SESSION['user_role'] === $roles;
    return in_array($_SESSION['user_role'], $roles);
}

function require_auth($roles = []) {
    if (!is_logged_in()) {
        $_SESSION['alert_err'] = "Please login to access this page.";
        header("Location: login.php");
        exit;
    }
    if (!empty($roles) && !has_role($roles)) {
        $_SESSION['alert_err'] = "You are not authorized to view this page.";
        header("Location: index.php");
        exit;
    }
}

function show_alerts() {
    $html = '';
    if (isset($_SESSION['alert_success'])) {
        $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($_SESSION['alert_success']) . '
                    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                  </div>';
        unset($_SESSION['alert_success']);
    }
    if (isset($_SESSION['alert_err'])) {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($_SESSION['alert_err']) . '
                    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                  </div>';
        unset($_SESSION['alert_err']);
    }
    return $html;
}

function generate_download_token($order_id, $product_id) {
    $key    = "CHANGE_THIS_TO_A_STRONG_RANDOM_SECRET_KEY";  // Change this!
    $expiry = time() + (3600 * 24 * 7);
    $hash   = hash_hmac('sha256', "$order_id|$product_id|$expiry", $key);
    return base64_encode("$order_id|$product_id|$expiry|$hash");
}

function validate_download_token($token) {
    $decoded = base64_decode($token);
    if (!$decoded) return false;
    $parts = explode('|', $decoded);
    if (count($parts) !== 4) return false;
    list($order_id, $product_id, $expiry, $hash) = $parts;
    if (time() > $expiry) return false;
    $key           = "CHANGE_THIS_TO_A_STRONG_RANDOM_SECRET_KEY";  // Must match above
    $expected_hash = hash_hmac('sha256', "$order_id|$product_id|$expiry", $key);
    if (!hash_equals($expected_hash, $hash)) return false;
    return ['order_id' => $order_id, 'product_id' => $product_id];
}
?>
