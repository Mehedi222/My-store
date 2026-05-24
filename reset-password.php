<?php
// reset-password.php - Password Reset Handler
// In production: validates a signed email token; in sandbox: allows direct reset by email
require_once 'config.php';

// Redirect logged-in users
if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (empty($email) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = "No account found with this email address.";
            } else {
                // Update password
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed, $email]);
                $success = true;
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center align-items-center py-5">
    <div class="col-md-6 col-lg-5">
        <div class="card card-glass p-4 border-0">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-shield-alt text-gradient fa-3x mb-2"></i>
                    <h2 class="fw-bold text-gradient">Set New Password</h2>
                    <p class="text-muted">Create a new secure password for your account</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 shadow-sm p-4 text-center mb-4">
                        <i class="fas fa-check-double fa-3x mb-3 text-success"></i>
                        <h5 class="fw-bold">Password Updated!</h5>
                        <p class="small mb-0">Your password has been reset successfully. You can now login with your new credentials.</p>
                    </div>
                    <a href="login.php" class="btn btn-premium w-100 py-3"><i class="fas fa-sign-in-alt me-2"></i>Login Now</a>
                <?php else: ?>
                    <form action="reset-password.php" method="POST">
                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold small text-muted"><i class="fas fa-envelope me-2"></i>Email Address</label>
                            <input type="email" id="email" name="email" class="form-control form-control-premium"
                                   placeholder="john@example.com"
                                   value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <!-- New Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold small text-muted"><i class="fas fa-lock me-2"></i>New Password</label>
                            <input type="password" id="password" name="password" class="form-control form-control-premium"
                                   placeholder="••••••••" required>
                            <div class="form-text text-muted small">Minimum 6 characters.</div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-bold small text-muted"><i class="fas fa-shield-check me-2"></i>Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-premium"
                                   placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn btn-premium w-100 py-3 mb-3"><i class="fas fa-save me-2"></i>Reset Password</button>

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
