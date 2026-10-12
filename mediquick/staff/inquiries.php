<?php
/**
 * STAFF: RESPOND TO INQUIRIES
 * ---------------------------------------------------------------
 * Every message a customer sends via Contact Us ends up here. Once
 * staff type a reply and submit it, the inquiry's status flips from
 * 'open' to 'answered' and the reply becomes visible to that customer
 * on their own Contact Us / My Inquiries page.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('staff');
define('BASE_URL', '../');
$page_title = 'Inquiries';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiry_id = (int)$_POST['inquiry_id'];
    $reply = trim($_POST['reply']);
    $stmt = $pdo->prepare("UPDATE inquiries SET reply = ?, status = 'answered' WHERE inquiry_id = ?");
    $stmt->execute([$reply, $inquiry_id]);
    header("Location: inquiries.php");
    exit;
}

// ORDER BY status ASC puts 'answered' after 'open' alphabetically,
// so unanswered messages naturally rise to the top of the page.
$inquiries = $pdo->query("SELECT iq.*, u.full_name, u.email FROM inquiries iq JOIN users u ON iq.user_id = u.user_id ORDER BY iq.status ASC, iq.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Customer Inquiries</h1>

<?php foreach ($inquiries as $iq): ?>
    <div class="card-box">
        <p><strong><?php echo htmlspecialchars($iq['subject']); ?></strong>
        <span class="status-pill status-<?php echo $iq['status'] === 'answered' ? 'approved' : 'pending'; ?>"><?php echo ucfirst($iq['status']); ?></span></p>
        <p><?php echo htmlspecialchars($iq['full_name']); ?> (<?php echo htmlspecialchars($iq['email']); ?>)</p>
        <p><?php echo nl2br(htmlspecialchars($iq['message'])); ?></p>

        <?php if ($iq['status'] === 'open'): ?>
            <form method="POST" action="inquiries.php">
                <input type="hidden" name="inquiry_id" value="<?php echo $iq['inquiry_id']; ?>">
                <textarea name="reply" rows="2" placeholder="Type your reply..." required></textarea>
                <button type="submit" class="btn">Send Reply</button>
            </form>
        <?php else: ?>
            <p><strong>Reply:</strong> <?php echo nl2br(htmlspecialchars($iq['reply'])); ?></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>
