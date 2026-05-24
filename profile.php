<?php
// profile.php - User Profile Management Page
require_once 'includes/header.php';

// Force authentication
require_auth();

$user = get_logged_in_user();
$db_user = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $db_user = $stmt->fetch();
} catch (PDOException $e) {
    $_SESSION['alert_err'] = "Database error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($name) || empty($email)) {
        $_SESSION['alert_err'] = "Name and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['alert_err'] = "Please enter a valid email address.";
    } else {
        try {
            // Check if email taken by someone else
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $_SESSION['alert_err'] = "Email is already in use by another account.";
            } else {
                $sql = "UPDATE users SET name = ?, email = ?";
                $params = [$name, $email];
                
                // If modifying password
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $_SESSION['alert_err'] = "Password must be at least 6 characters long.";
                    } elseif ($password !== $confirm_password) {
                        $_SESSION['alert_err'] = "Passwords do not match.";
                    } else {
                        $sql .= ", password = ?";
                        $params[] = password_hash($password, PASSWORD_BCRYPT);
                    }
                }
                
                if (!isset($_SESSION['alert_err'])) {
                    $sql .= " WHERE id = ?";
                    $params[] = $user['id'];
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    
                    // Sync Session
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    $_SESSION['alert_success'] = "Profile updated successfully!";
                    header("Location: profile.php");
                    exit;
                }
            }
        } catch (PDOException $e) {
            $_SESSION['alert_err'] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="row">
    <!-- User Side Sidebar (Responsive Layout) -->
    <div class="col-md-4 mb-4">
        <div class="card card-glass border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-circle fa-5x text-gradient mb-3"></i>
                <h4 class="fw-bold text-gradient"><?php echo htmlspecialchars($db_user['name']); ?></h4>
                <span class="badge bg-primary px-3 py-2 rounded-pill"><?php echo strtoupper($db_user['role']); ?></span>
                <p class="text-muted small mt-2">Member since: <?php echo date('M d, Y', strtotime($db_user['created_at'])); ?></p>
                
                <hr class="my-4">
                
                <div class="list-group list-group-light text-start shadow-0">
                    <a href="profile.php" class="list-group-item list-group-item-action active px-3 border-0 rounded-3 mb-2">
                        <i class="fas fa-user-edit me-3"></i>Edit Profile
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-2">
                        <i class="fas fa-box-open me-3"></i>My Purchases
                    </a>
                    <a href="contact.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3">
                        <i class="fas fa-question-circle me-3"></i>Get Help
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Edit Form -->
    <div class="col-md-8">
        <div class="card card-glass border-0 p-4">
            <div class="card-body">
                <h3 class="fw-bold text-gradient mb-4">Edit Profile Settings</h3>
                
                <form action="profile.php" method="POST">
                    <div class="row">
                        <!-- Name Input -->
                        <div class="col-md-6 mb-4">
                            <label for="name" class="form-label fw-bold small text-muted"><i class="fas fa-user me-2"></i>Full Name</label>
                            <input type="text" id="name" name="name" class="form-control form-control-premium" placeholder="John Doe" value="<?php echo htmlspecialchars($db_user['name']); ?>" required>
                        </div>
                        
                        <!-- Email Input -->
                        <div class="col-md-6 mb-4">
                            <label for="email" class="form-label fw-bold small text-muted"><i class="fas fa-envelope me-2"></i>Email Address</label>
                            <input type="email" id="email" name="email" class="form-control form-control-premium" placeholder="john@example.com" value="<?php echo htmlspecialchars($db_user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Password Input -->
                        <div class="col-md-6 mb-4">
                            <label for="password" class="form-label fw-bold small text-muted"><i class="fas fa-lock me-2"></i>New Password (Optional)</label>
                            <input type="password" id="password" name="password" class="form-control form-control-premium" placeholder="••••••••">
                            <div class="form-text text-muted small">Leave blank to keep current password.</div>
                        </div>
                        
                        <!-- Confirm Password Input -->
                        <div class="col-md-6 mb-4">
                            <label for="confirm_password" class="form-label fw-bold small text-muted"><i class="fas fa-shield-alt me-2"></i>Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-premium" placeholder="••••••••">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-premium py-3 px-5 mt-2">Save Profile Updates</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
