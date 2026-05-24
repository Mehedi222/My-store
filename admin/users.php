<?php
// admin/users.php - Admin User Manager & Customer Log
require_once 'includes/header.php';

// Handle Block/Unblock toggle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['id']);
    
    // Prevent blocking oneself
    if ($user_id == $user['id']) {
        $_SESSION['alert_err'] = "You cannot change your own block status!";
    } else {
        try {
            $new_status = $action === 'block' ? 'blocked' : 'active';
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_id]);
            $_SESSION['alert_success'] = "User status has been successfully updated to: " . strtoupper($new_status);
            header("Location: users.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['alert_err'] = "Failed to update user status: " . $e->getMessage();
        }
    }
}

// Retrieve registered users
$registered_users = [];
try {
    $registered_users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    // Fail silent
}
?>

<div class="row g-4 mb-4">
    <!-- Header Banner -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4">
            <h2 class="fw-bold mb-1"><i class="fas fa-users me-2 text-primary"></i>Registered Customer Accounts</h2>
            <p class="text-muted mb-0 small">Block or unblock customer logs, verify emails, and view complete transaction/purchase history per customer.</p>
        </div>
    </div>
    
    <!-- Users Listing Table -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4">
            <h4 class="fw-bold mb-4">Customer Directory</h4>
            
            <?php if (!empty($registered_users)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase border-bottom border-color">
                                <th>Name &amp; Profile</th>
                                <th>Email Address</th>
                                <th>Security Role</th>
                                <th>Billing History</th>
                                <th>Status</th>
                                <th class="text-end">Account Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registered_users as $cust): 
                                $status_badge = $cust['status'] === 'active' ? 'bg-success' : 'bg-danger';
                                $role_badge = $cust['role'] === 'admin' ? 'bg-primary' : ($cust['role'] === 'editor' ? 'bg-info' : 'bg-secondary');
                                
                                // Fetch Purchase History Stats per User
                                $total_spent = 0;
                                $orders_cnt = 0;
                                try {
                                    $stmt_spent = $pdo->prepare("SELECT COUNT(id) as cnt, SUM(final_amount) as spent FROM orders WHERE user_id = ? AND payment_status = 'completed'");
                                    $stmt_spent->execute([$cust['id']]);
                                    $spent_stats = $stmt_spent->fetch();
                                    $orders_cnt = intval($spent_stats['cnt']);
                                    $total_spent = floatval($spent_stats['spent']);
                                } catch (PDOException $e) {
                                    // Silent fail
                                }
                            ?>
                                <tr class="border-bottom border-color">
                                    <td>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($cust['name']); ?></h6>
                                        <span class="small text-muted">Created: <?php echo date('M d, Y', strtotime($cust['created_at'])); ?></span>
                                    </td>
                                    <td class="small"><?php echo htmlspecialchars($cust['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $role_badge; ?> px-2.5 py-1 rounded shadow-0 small"><?php echo strtoupper($cust['role']); ?></span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <strong>Spent:</strong> <span class="text-gradient fw-bold"><?php echo format_price($total_spent); ?></span> <br>
                                            <span class="text-muted">Cleared Orders: <?php echo $orders_cnt; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $status_badge; ?> px-2.5 py-1 rounded shadow-0 small"><?php echo strtoupper($cust['status']); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <!-- View detailed orders dropdown menu -->
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-outline-primary btn-sm rounded-pill p-2 dropdown-toggle shadow-0" type="button" data-mdb-toggle="dropdown" aria-expanded="false" <?php echo $orders_cnt === 0 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-history"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end p-3 shadow-lg" style="width: 250px;">
                                                <h6 class="fw-bold mb-2">Purchase History:</h6>
                                                <?php 
                                                // Fetch individual user order lists
                                                try {
                                                    $stmt_hist = $pdo->prepare("SELECT id, final_amount, created_at FROM orders WHERE user_id = ? AND payment_status = 'completed' ORDER BY id DESC LIMIT 5");
                                                    $stmt_hist->execute([$cust['id']]);
                                                    while ($o = $stmt_hist->fetch()) {
                                                        echo "<li class='small text-muted mb-1 border-bottom border-color pb-1'>
                                                                Order #".$o['id']." - <strong>".format_price($o['final_amount'])."</strong> <br>
                                                                <span class='text-muted' style='font-size: 10px;'>".date('Y-m-d', strtotime($o['created_at']))."</span>
                                                              </li>";
                                                    }
                                                } catch (PDOException $e) {}
                                                ?>
                                            </ul>
                                        </div>
                                        
                                        <!-- Block / Unblock toggler -->
                                        <?php if ($cust['id'] != $user['id']): ?>
                                            <?php if ($cust['status'] === 'active'): ?>
                                                <a href="users.php?action=block&id=<?php echo $cust['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1.5 small text-uppercase fw-bold" onclick="return confirm('Are you sure you want to block <?php echo htmlspecialchars($cust['name']); ?>? They will be unable to login.');">
                                                    Block
                                                </a>
                                            <?php else: ?>
                                                <a href="users.php?action=unblock&id=<?php echo $cust['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill px-2.5 py-1.5 small text-uppercase fw-bold">
                                                    Unblock
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted text-center py-5">No customer accounts registered.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
