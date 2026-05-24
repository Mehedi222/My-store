<?php
// signup.php - User Registration Page
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['alert_err'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['alert_err'] = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $_SESSION['alert_err'] = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['alert_err'] = "Email is already registered. Please login instead.";
            } else {
                // Hash Password using standard bcrypt
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert User
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$name, $email, $hashed_password]);
                
                $_SESSION['alert_success'] = "Registration successful! You can now login.";
                header("Location: login.php");
                exit;
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
                    <h2 class="fw-bold text-gradient">Create Account</h2>
                    <p class="text-muted">Start purchasing premium digital products</p>
                </div>
                
                <form action="signup.php" method="POST">
                    <!-- Name Input -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold small text-muted"><i class="fas fa-user me-2"></i>Full Name</label>
                        <input type="text" id="name" name="name" class="form-control form-control-premium" placeholder="John Doe" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    
                    <!-- Email Input -->
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold small text-muted"><i class="fas fa-envelope me-2"></i>Email Address</label>
                        <input type="email" id="email" name="email" class="form-control form-control-premium" placeholder="john@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <!-- Password Input -->
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold small text-muted"><i class="fas fa-lock me-2"></i>Password</label>
                        <input type="password" id="password" name="password" class="form-control form-control-premium" placeholder="••••••••" required>
                        <div class="form-text text-muted small">Minimum 6 characters.</div>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" class="btn btn-premium w-100 py-3 mb-3">Sign Up</button>
                    
                    <!-- Login Link -->
                    <div class="text-center">
                        <span class="text-muted small">Already have an account?</span>
                        <a href="login.php" class="fw-bold small ms-1 text-primary">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
