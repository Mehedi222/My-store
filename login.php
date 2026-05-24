<?php
// login.php - User Login Page
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['alert_err'] = "Both fields are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'blocked') {
                    $_SESSION['alert_err'] = "Your account has been blocked by administrators.";
                } else {
                    // Set Session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    $_SESSION['alert_success'] = "Welcome back, " . htmlspecialchars($user['name']) . "!";
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin' || $user['role'] === 'editor') {
                        header("Location: admin/index.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                }
            } else {
                $_SESSION['alert_err'] = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $_SESSION['alert_err'] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center align-items-center py-5">
    <div class="col-md-6 col-lg-5">
        <div class="card card-glass p-4 border-0">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-cubes text-gradient fa-3x mb-2"></i>
                    <h2 class="fw-bold text-gradient">Welcome Back</h2>
                    <p class="text-muted">Sign in to download purchased products</p>
                </div>
                
                <form action="login.php" method="POST">
                    <!-- Email Input -->
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold small text-muted"><i class="fas fa-envelope me-2"></i>Email Address</label>
                        <input type="email" id="email" name="email" class="form-control form-control-premium" placeholder="john@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <!-- Password Input -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <label for="password" class="form-label fw-bold small text-muted"><i class="fas fa-lock me-2"></i>Password</label>
                            <a href="forgot-password.php" class="small text-primary">Forgot Password?</a>
                        </div>
                        <input type="password" id="password" name="password" class="form-control form-control-premium" placeholder="••••••••" required>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" class="btn btn-premium w-100 py-3 mb-3">Sign In</button>
                    
                    <!-- Signup Link -->
                    <div class="text-center">
                        <span class="text-muted small">Don't have an account?</span>
                        <a href="signup.php" class="fw-bold small ms-1 text-primary">Create one here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
