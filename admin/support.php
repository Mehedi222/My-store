<?php
// admin/support.php - Admin Support Inbox & FAQs Manager
require_once 'includes/header.php';

// Handle FAQ deletion
if (isset($_GET['delete_faq'])) {
    $faq_id = intval($_GET['delete_faq']);
    try {
        $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$faq_id]);
        $_SESSION['alert_success'] = "FAQ item deleted successfully!";
        header("Location: support.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert_err'] = "Failed to delete FAQ: " . $e->getMessage();
    }
}

// Handle Add FAQ form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_faq'])) {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    
    if (empty($question) || empty($answer)) {
        $_SESSION['alert_err'] = "Both question and answer are required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
            $stmt->execute([$question, $answer]);
            $_SESSION['alert_success'] = "New FAQ item added successfully!";
            header("Location: support.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['alert_err'] = "Failed to save FAQ: " . $e->getMessage();
        }
    }
}

// Handle Ticket Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $reply = trim($_POST['reply']);
    $status = $_POST['status'];
    
    if (empty($reply)) {
        $_SESSION['alert_err'] = "Reply message cannot be empty.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE tickets SET reply = ?, status = ? WHERE id = ?");
            $stmt->execute([$reply, $status, $ticket_id]);
            $_SESSION['alert_success'] = "Reply sent and ticket updated successfully!";
            header("Location: support.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['alert_err'] = "Failed to submit reply: " . $e->getMessage();
        }
    }
}

// Retrieve Tickets
$tickets = [];
try {
    $tickets = $pdo->query("SELECT * FROM tickets ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    // Fail silent
}

// Retrieve FAQs
$faqs = [];
try {
    $faqs = $pdo->query("SELECT * FROM faqs ORDER BY id ASC")->fetchAll();
} catch (PDOException $e) {
    // Fail silent
}

// Selected Ticket for replying
$reply_ticket = null;
if (isset($_GET['reply_to'])) {
    $ticket_id = intval($_GET['reply_to']);
    foreach ($tickets as $t) {
        if ($t['id'] == $ticket_id) {
            $reply_ticket = $t;
            break;
        }
    }
}
?>

<div class="row g-4 mb-4">
    <!-- Inbox Banner -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4">
            <h2 class="fw-bold mb-1"><i class="fas fa-headset me-2 text-primary"></i>Customer Support Inbox &amp; FAQs</h2>
            <p class="text-muted mb-0 small">Respond to client inquiries, answer tickets, and update storefront Frequently Asked Questions dynamically.</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Support Tickets list & reply -->
    <div class="col-lg-7 mb-4">
        <div class="card card-glass border-0 p-4 mb-4">
            <h4 class="fw-bold mb-4"><i class="fas fa-inbox me-2 text-primary"></i>Inbound Support Tickets</h4>
            
            <?php if (!empty($tickets)): ?>
                <div class="list-group list-group-light shadow-0">
                    <?php foreach ($tickets as $t): 
                        $status_color = $t['status'] === 'open' ? 'bg-danger' : ($t['status'] === 'replied' ? 'bg-info' : 'bg-success');
                    ?>
                        <div class="list-group-item px-0 border-bottom border-color py-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge <?php echo $status_color; ?> px-2.5 py-1 rounded shadow-0 small"><?php echo strtoupper($t['status']); ?></span>
                                <span class="small text-muted"><?php echo date('Y-m-d H:i', strtotime($t['created_at'])); ?></span>
                            </div>
                            <h5 class="fw-bold mb-1">Subject: <?php echo htmlspecialchars($t['subject']); ?></h5>
                            <p class="small text-muted mb-2">From: <strong><?php echo htmlspecialchars($t['name']); ?></strong> (<?php echo htmlspecialchars($t['email']); ?>)</p>
                            <p class="small bg-light p-3 rounded border border-color my-2 text-dark"><?php echo nl2br(htmlspecialchars($t['message'])); ?></p>
                            
                            <?php if (!empty($t['reply'])): ?>
                                <p class="small bg-light p-3 rounded border border-color border-start-0 border-top-0 border-bottom-0 border-primary border-4 my-2 text-muted" style="background: var(--bg-color) !important;">
                                    <strong>Reply Sent:</strong> <br>
                                    <?php echo nl2br(htmlspecialchars($t['reply'])); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="text-end mt-2">
                                <a href="support.php?reply_to=<?php echo $t['id']; ?>" class="btn btn-primary btn-sm py-1.5 px-3 rounded-pill"><i class="fas fa-reply me-1"></i>Respond</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-muted text-center py-5">No support tickets logged.</div>
            <?php endif; ?>
        </div>
        
        <!-- Interactive Reply Form Box -->
        <?php if ($reply_ticket): ?>
            <div class="card card-glass border-0 p-4 border-start border-primary border-4">
                <h4 class="fw-bold mb-3 text-gradient"><i class="fas fa-reply me-2"></i>Reply to ticket #<?php echo $reply_ticket['id']; ?></h4>
                <p class="small text-muted mb-3">Responding to: <strong><?php echo htmlspecialchars($reply_ticket['subject']); ?></strong> by <?php echo htmlspecialchars($reply_ticket['name']); ?></p>
                
                <form action="support.php" method="POST">
                    <input type="hidden" name="ticket_id" value="<?php echo $reply_ticket['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="reply" class="form-label fw-bold small text-muted">Response Message</label>
                        <textarea id="reply" name="reply" class="form-control form-control-premium" rows="5" placeholder="Type response..." required><?php echo htmlspecialchars($reply_ticket['reply']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="status" class="form-label fw-bold small text-muted">Update Status</label>
                            <select id="status" name="status" class="form-select form-control-premium" required>
                                <option value="replied" <?php echo $reply_ticket['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="closed" <?php echo $reply_ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed (Completed)</option>
                                <option value="open" <?php echo $reply_ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" name="send_reply" class="btn btn-premium py-2.5 px-4"><i class="fas fa-paper-plane me-2"></i>Send Response</button>
                    <a href="support.php" class="btn btn-outline-primary rounded-pill py-2.5 px-3 ms-2">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Column: FAQs Manager CRUD -->
    <div class="col-lg-5 mb-4">
        <!-- Add FAQ Form -->
        <div class="card card-glass border-0 p-4 mb-4">
            <h4 class="fw-bold mb-4 text-gradient"><i class="fas fa-plus me-2"></i>Create FAQ Item</h4>
            
            <form action="support.php" method="POST">
                <div class="mb-3">
                    <label for="question" class="form-label fw-bold small text-muted">Question</label>
                    <input type="text" id="question" name="question" class="form-control form-control-premium" placeholder="e.g. How can I request a refund?" required>
                </div>
                
                <div class="mb-3">
                    <label for="answer" class="form-label fw-bold small text-muted">Answer</label>
                    <textarea id="answer" name="answer" class="form-control form-control-premium" rows="4" placeholder="Enter answers..." required></textarea>
                </div>
                
                <button type="submit" name="save_faq" class="btn btn-premium w-100 py-3"><i class="fas fa-save me-2"></i>Save FAQ</button>
            </form>
        </div>
        
        <!-- FAQs Catalog list -->
        <div class="card card-glass border-0 p-4">
            <h4 class="fw-bold mb-4"><i class="fas fa-list-ol me-2 text-primary"></i>Storefront FAQs</h4>
            
            <?php if (!empty($faqs)): ?>
                <div class="list-group list-group-light shadow-0">
                    <?php foreach ($faqs as $f): ?>
                        <div class="list-group-item px-0 border-bottom border-color py-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-1" style="max-width: 80%;"><?php echo htmlspecialchars($f['question']); ?></h6>
                                <a href="support.php?delete_faq=<?php echo $f['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill p-1 hover-scale" onclick="return confirm('Delete this FAQ?');"><i class="fas fa-trash"></i></a>
                            </div>
                            <p class="small text-muted mb-0 leading-relaxed"><?php echo htmlspecialchars($f['answer']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-muted text-center py-5">No FAQ articles have been cataloged.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
