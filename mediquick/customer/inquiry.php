<?php
/**
 * CUSTOMER INQUIRY / CONTACT HISTORY PAGE
 * ---------------------------------------------------------------
 * Lets a logged-in customer send a message to the pharmacy and see
 * their own past messages plus any staff replies. The public-facing
 * Contact Us page (customer/contact.php) links here for anyone who's
 * already logged in.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('customer');
define('BASE_URL', '../');
$page_title = 'Contact Us';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($subject)) $errors[] = "Subject is required.";
    if (empty($message)) $errors[] = "Message cannot be empty.";

    if (empty($errors)) {
        // status defaults to 'open' (see database schema) — staff pick
        // this up from staff/inquiries.php and mark it 'answered' once
        // they've replied.
        $stmt = $pdo->prepare("INSERT INTO inquiries (user_id, subject, message) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $subject, $message]);
        $success = true;
    }
}

$stmt = $pdo->prepare("SELECT * FROM inquiries WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Contact Us</h1>

<div class="form-container" style="margin:0 0 30px;max-width:100%;">
    <?php if ($success): ?>
        <div class="success-box">Your inquiry has been submitted. We'll respond soon.</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="error-box"><ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="inquiry.php">
        <label>Subject</label>
        <input type="text" name="subject" required>
        <label>Message</label>
        <textarea name="message" rows="4" required></textarea>
        <button type="submit" class="btn">Submit</button>
    </form>
</div>

<h2>Your Past Inquiries</h2>
<?php if (empty($inquiries)): ?>
    <p>No inquiries yet.</p>
<?php else: ?>
    <?php foreach ($inquiries as $iq): ?>
        <div class="card-box">
            <strong><?php echo htmlspecialchars($iq['subject']); ?></strong>
            <!-- Reuse the same "approved" green colour for "answered" so we
                 don't need a brand new CSS class just for this one status. -->
            <span class="status-pill status-<?php echo $iq['status'] === 'answered' ? 'approved' : 'pending'; ?>"><?php echo ucfirst($iq['status']); ?></span>
            <p><?php echo nl2br(htmlspecialchars($iq['message'])); ?></p>
            <?php if ($iq['reply']): ?>
                <p><strong>Reply:</strong> <?php echo nl2br(htmlspecialchars($iq['reply'])); ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
