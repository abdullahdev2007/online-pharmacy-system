<?php
/**
 * STAFF: VERIFY PRESCRIPTIONS
 * ---------------------------------------------------------------
 * This is where the "verification" half of the prescription workflow
 * happens (the "upload" half is customer/upload_prescription.php).
 * A staff member opens the uploaded file, reads it, and clicks either
 * Approve or Reject. Only prescriptions marked 'approved' here can
 * later be selected by a customer at checkout.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('staff');
define('BASE_URL', '../');
$page_title = 'Verify Prescriptions';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prescription_id = (int)$_POST['prescription_id'];
    $action = $_POST['action']; // 'approve' or 'reject', from the two submit buttons below
    $notes = trim($_POST['notes'] ?? '');
    $status = $action === 'approve' ? 'approved' : 'rejected';

    // We record WHO reviewed it (reviewed_by) and WHEN (reviewed_at),
    // which matters for accountability if a decision is ever queried.
    $stmt = $pdo->prepare("UPDATE prescriptions SET status = ?, reviewed_by = ?, review_notes = ?, reviewed_at = NOW() WHERE prescription_id = ?");
    $stmt->execute([$status, $_SESSION['user_id'], $notes, $prescription_id]);
    header("Location: prescriptions.php");
    exit;
}

// The page can be filtered by status using ?status=pending / approved / rejected
// in the URL — this is what the three tab-like links below do.
$filter = $_GET['status'] ?? 'pending';
$stmt = $pdo->prepare("SELECT pr.*, u.full_name, u.email FROM prescriptions pr JOIN users u ON pr.user_id = u.user_id WHERE pr.status = ? ORDER BY pr.uploaded_at ASC");
$stmt->execute([$filter]);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Verify Prescriptions</h1>

<div class="filter-bar">
    <a href="?status=pending" class="btn <?php echo $filter == 'pending' ? '' : 'secondary'; ?>">Pending</a>
    <a href="?status=approved" class="btn <?php echo $filter == 'approved' ? '' : 'secondary'; ?>">Approved</a>
    <a href="?status=rejected" class="btn <?php echo $filter == 'rejected' ? '' : 'secondary'; ?>">Rejected</a>
</div>

<?php if (empty($prescriptions)): ?>
    <p>No <?php echo $filter; ?> prescriptions.</p>
<?php else: ?>
    <?php foreach ($prescriptions as $rx): ?>
        <div class="card-box">
            <p><strong>#<?php echo $rx['prescription_id']; ?></strong> — <?php echo htmlspecialchars($rx['full_name']); ?> (<?php echo htmlspecialchars($rx['email']); ?>)</p>
            <p>Uploaded: <?php echo date('d M Y, h:i A', strtotime($rx['uploaded_at'])); ?></p>
            <!-- Opens the raw uploaded file (image or PDF) in a new tab so
                 staff can actually read the prescription before deciding. -->
            <p><a href="../<?php echo htmlspecialchars($rx['file_path']); ?>" target="_blank">View uploaded file</a></p>

            <?php if ($filter === 'pending'): ?>
                <!-- Approve and Reject are two different submit buttons in the
                     SAME form; $_POST['action'] tells the handler above which
                     one was clicked. -->
                <form method="POST" action="prescriptions.php">
                    <input type="hidden" name="prescription_id" value="<?php echo $rx['prescription_id']; ?>">
                    <label>Notes (optional)</label>
                    <input type="text" name="notes" placeholder="e.g. reason for rejection">
                    <div style="margin-top:10px;">
                        <button type="submit" name="action" value="approve" class="btn">Approve</button>
                        <button type="submit" name="action" value="reject" class="btn danger">Reject</button>
                    </div>
                </form>
            <?php else: ?>
                <span class="status-pill status-<?php echo $rx['status']; ?>"><?php echo ucfirst($rx['status']); ?></span>
                <?php if ($rx['review_notes']): ?><p>Notes: <?php echo htmlspecialchars($rx['review_notes']); ?></p><?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
