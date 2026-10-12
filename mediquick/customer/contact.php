<?php
/**
 * CONTACT US — public page (anyone can view pharmacy contact info).
 * The "send a message" form only appears for logged-in customers,
 * since messages are tied to a user_id in the inquiries table; a
 * logged-out visitor sees the contact details plus a prompt to log in.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
define('BASE_URL', '../');
$page_title = 'Contact Us';
$errors = [];
$success = false;

$is_customer = isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer';

if ($is_customer && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($subject)) $errors[] = "Subject is required.";
    if (empty($message)) $errors[] = "Message cannot be empty.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO inquiries (user_id, subject, message) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $subject, $message]);
        $success = true;
    }
}

include '../includes/header.php';
?>

<div class="card-box">
    <h1>Contact Us</h1>
    <p><strong>Address:</strong> No. 45, Kandy Road, Kurunegala, Sri Lanka</p>
    <p><strong>Phone:</strong> 037 222 1234</p>
    <p><strong>Email:</strong> support@mediquick.lk</p>
    <p><strong>Hours:</strong> Monday - Saturday, 8:00 AM - 8:00 PM</p>
</div>

<div class="form-container" style="margin:0;max-width:100%;">
    <h2>Send Us a Message</h2>

    <?php if (!$is_customer): ?>
        <p>Please <a href="login.php">login</a> or <a href="register.php">create an account</a> to send us a message directly. You're welcome to reach us by phone or email in the meantime.</p>
    <?php else: ?>
        <?php if ($success): ?>
            <div class="success-box">Your message has been submitted. We'll respond soon.</div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="error-box"><ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul></div>
        <?php endif; ?>
        <form method="POST" action="contact.php">
            <label>Subject</label>
            <input type="text" name="subject" required>
            <label>Message</label>
            <textarea name="message" rows="4" required></textarea>
            <button type="submit" class="btn">Submit</button>
        </form>
        <p style="margin-top:10px;font-size:14px;">View your past messages on the <a href="inquiry.php">Contact history</a> page.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
