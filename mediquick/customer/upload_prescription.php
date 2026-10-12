<?php
/**
 * PRESCRIPTION UPLOAD PAGE
 * ---------------------------------------------------------------
 * Lets a logged-in customer upload a photo or PDF of their doctor's
 * prescription. Every upload starts life with status = 'pending' and
 * stays invisible to the rest of the system (i.e. unusable at
 * checkout) until a staff member reviews and approves it on the
 * staff/prescriptions.php page.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_role('customer');
define('BASE_URL', '../');
$page_title = 'Upload Prescription';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['prescription_file'])) {
    $file = $_FILES['prescription_file'];

    // validate_prescription_upload() checks the file's real content
    // type and size — see includes/functions.php for why we check the
    // real MIME type rather than trusting the filename.
    $error = validate_prescription_upload($file);

    if ($error) {
        $errors[] = $error;
    } else {
        // Build a unique filename so two different customers' uploads
        // (or the same customer uploading twice) never overwrite each
        // other: user id + current timestamp + original extension.
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_name = 'rx_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $dest = '../uploads/prescriptions/' . $unique_name;

        // move_uploaded_file() is the safe way to save an uploaded
        // file in PHP — it double-checks the file genuinely came from
        // a real file upload (not, say, a path someone typed into the
        // form to try to read an arbitrary file on the server).
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $stmt = $pdo->prepare("INSERT INTO prescriptions (user_id, file_path, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], 'uploads/prescriptions/' . $unique_name]);
            $success = true;
        } else {
            $errors[] = "Failed to save the uploaded file. Please try again.";
        }
    }
}

// Show this customer's full upload history underneath the form, so
// they can see at a glance which ones are still pending.
$stmt = $pdo->prepare("SELECT * FROM prescriptions WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Upload Prescription</h1>

<div class="form-container" style="margin:0 0 30px;max-width:100%;">
    <?php if ($success): ?>
        <div class="success-box">Prescription uploaded successfully. Our staff will review it shortly.</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="error-box"><ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <!-- enctype="multipart/form-data" is required on any form that
         uploads a file — without it, the browser wouldn't include the
         file's actual bytes in the request. -->
    <form method="POST" action="upload_prescription.php" enctype="multipart/form-data">
        <label>Prescription File (JPG, PNG, or PDF — max 5MB)</label>
        <input type="file" name="prescription_file" accept=".jpg,.jpeg,.png,.pdf" required>
        <button type="submit" class="btn">Upload</button>
    </form>
</div>

<h2>Your Prescriptions</h2>
<?php if (empty($prescriptions)): ?>
    <p>You haven't uploaded any prescriptions yet.</p>
<?php else: ?>
    <table class="data-table">
        <thead><tr><th>ID</th><th>Uploaded</th><th>Status</th><th>Notes</th></tr></thead>
        <tbody>
        <?php foreach ($prescriptions as $rx): ?>
            <tr>
                <td>#<?php echo $rx['prescription_id']; ?></td>
                <td><?php echo date('d M Y, h:i A', strtotime($rx['uploaded_at'])); ?></td>
                <!-- The CSS class changes with the status value, e.g.
                     "status-pending" / "status-approved" / "status-rejected",
                     so each status gets its own colour automatically. -->
                <td><span class="status-pill status-<?php echo $rx['status']; ?>"><?php echo ucfirst($rx['status']); ?></span></td>
                <td><?php echo htmlspecialchars($rx['review_notes'] ?? '-'); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
