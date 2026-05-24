<?php
// contact.php - Contact Support, Tickets Submission and FAQs Portal
require_once 'includes/header.php';

$name = $email = $subject = $message = '';
$ticket_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['alert_err'] = "All contact form fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['alert_err'] = "Please enter a valid email address.";
    } else {
        $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
        try {
            $stmt = $pdo->prepare("INSERT INTO tickets (user_id, name, email, subject, message, status) VALUES (?, ?, ?, ?, ?, 'open')");
            $stmt->execute([$user_id, $name, $email, $subject, $message]);
            
            $ticket_success = true;
            $_SESSION['alert_success'] = "Support ticket submitted successfully! Reference logged.";
        } catch (PDOException $e) {
            $_SESSION['alert_err'] = "Failed to submit ticket: " . $e->getMessage();
        }
    }
}

// Fetch FAQs
$faqs = [];
try {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY id ASC");
    $faqs = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fail silent
}

// If logged in, prefill name and email
if (is_logged_in() && empty($name)) {
    $curr_user = get_logged_in_user();
    $name = $curr_user['name'];
    $email = $curr_user['email'];
}
?>

<div class="row g-4 mb-4">
    <!-- Header Title Banner -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4 mb-2">
            <h2 class="fw-bold mb-1"><i class="fas fa-question-circle me-2 text-primary"></i>Customer Support &amp; FAQs</h2>
            <p class="text-muted mb-0 small">Get immediate answers from FAQs or submit a ticket to our administration desk.</p>
        </div>
    </div>
    
    <!-- Left Column - Dynamic FAQ Accordions -->
    <div class="col-lg-7">
        <div class="card card-glass border-0 p-4 h-100">
            <h3 class="fw-bold mb-4 text-gradient"><i class="fas fa-list-ol me-2"></i>Frequently Asked Questions</h3>
            
            <div class="accordion accordion-flush" id="contactFaqAccordion">
                <?php if (!empty($faqs)): ?>
                    <?php foreach ($faqs as $faq): ?>
                        <div class="accordion-item card-glass border-0 mb-3 overflow-hidden shadow-0" style="border: 1px solid var(--border-color) !important;">
                            <h2 class="accordion-header" id="contact_heading_<?php echo $faq['id']; ?>">
                                <button class="accordion-button collapsed fw-bold text-reset shadow-0" type="button" data-mdb-toggle="collapse" data-mdb-target="#contact_collapse_<?php echo $faq['id']; ?>" aria-expanded="false" aria-controls="contact_collapse_<?php echo $faq['id']; ?>" style="background: transparent; color: var(--text-color);">
                                    <i class="fas fa-question-circle me-2 text-primary"></i><?php echo htmlspecialchars($faq['question']); ?>
                                </button>
                            </h2>
                            <div id="contact_collapse_<?php echo $faq['id']; ?>" class="accordion-collapse collapse" aria-labelledby="contact_heading_<?php echo $faq['id']; ?>" data-mdb-parent="#contactFaqAccordion">
                                <div class="accordion-body text-muted small pt-0 pb-3 px-4">
                                    <?php echo htmlspecialchars($faq['answer']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted text-center py-5">No FAQ articles have been configured.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Submit Support Ticket -->
    <div class="col-lg-5">
        <div class="card card-glass border-0 p-4">
            <h3 class="fw-bold mb-4 text-gradient"><i class="fas fa-paper-plane me-2"></i>Open a Ticket</h3>
            
            <?php if ($ticket_success): ?>
                <div class="alert alert-success border-0 shadow-sm p-4 text-center">
                    <i class="fas fa-check-double fa-3x mb-3 text-success"></i>
                    <h5 class="fw-bold">Ticket Submitted!</h5>
                    <p class="small mb-0">Our administration team has been notified. We usually respond within 12-24 hours. A record of this query has been linked to your account.</p>
                </div>
            <?php else: ?>
                <form action="contact.php" method="POST">
                    <!-- Name Input -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold small text-muted">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control form-control-premium" placeholder="John Doe" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    
                    <!-- Email Input -->
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold small text-muted">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control form-control-premium" placeholder="john@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <!-- Subject Input -->
                    <div class="mb-4">
                        <label for="subject" class="form-label fw-bold small text-muted">Subject / Issue Type</label>
                        <input type="text" id="subject" name="subject" class="form-control form-control-premium" placeholder="e.g. Refund dispute, Download failure" value="<?php echo htmlspecialchars($subject); ?>" required>
                    </div>
                    
                    <!-- Message Textarea -->
                    <div class="mb-4">
                        <label for="message" class="form-label fw-bold small text-muted">Detailed Message</label>
                        <textarea id="message" name="message" class="form-control form-control-premium" rows="5" placeholder="Explain your inquiry in detail..." required><?php echo htmlspecialchars($message); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-premium w-100 py-3"><i class="fas fa-share-square me-2"></i>Send Support Query</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
