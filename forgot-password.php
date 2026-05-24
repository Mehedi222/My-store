<?php
// forgot-password.php - Simulated Password Recovery Page
require_once 'includes/header.php';

$success = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $_SESSION['alert_err'] = "Please enter your email address.";
    } else {
        // Mock email dispatch successfully
        $success = true;
    }
}
?>

<div class="row justify-content-center align-items-center py-5">
    <div class="col-md-6 col-lg-5">
        <div class="card card-glass p-4 border-0">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-key text-gradient fa-3x mb-2"></i>
                    <h2 class="fw-bold text-gradient">Reset Password</h2>
                    <p class="text-muted">Enter your email to receive recovery instructions</p>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success border-0 shadow-sm p-4 mb-4 text-center">
                        <i class="fas fa-paper-plane fa-2x mb-3 text-success"></i>
                        <h5 class="fw-bold">Instructions Dispatched!</h5>
                        <p class="small mb-3">We have simulated sending a secure recovery link to <strong><?php echo htmlspecialchars($email); ?></strong>. In production, a reset link would be emailed.</p>
                    </div>
                    <a href="reset-password.php?email=<?php echo urlencode($email); ?>" class="btn btn-premium w-100 py-3 mb-2"><i class="fas fa-key me-2"></i>Proceed to Reset Password</a>
                    <a href="login.php" class="btn btn-outline-primary rounded-pill w-100 py-2 mt-1">Back to Login</a>
                <?php else: ?>
                    <form action="forgot-password.php" method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold small text-muted"><i class="fas fa-envelope me-2"></i>Email Address</label>
                            <input type="email" id="email" name="email" class="form-control form-control-premium" placeholder="john@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-premium w-100 py-3 mb-3">Send Reset Instructions</button>
                        
                        <div class="text-center">
                            <a href="login.php" class="fw-bold small text-primary">Back to Login</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
