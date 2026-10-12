<?php
/**
 * MY PROFILE PAGE
 * ---------------------------------------------------------------
 * Two independent forms on one page: one to edit basic account
 * details (name/phone/address), and a separate one to change the
 * password. They're kept as two separate <form> submissions (not one
 * combined form) so that saving your address doesn't accidentally
 * also require re-entering your password, and vice versa.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('customer');
define('BASE_URL', '../');
$page_title = 'My Profile';
$errors = [];
$success = false;

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// --- Form 1: update name / phone / address -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (empty($full_name)) $errors[] = "Full name is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $phone, $address, $_SESSION['user_id']]);

        // Keep the session's copy of full_name in sync immediately,
        // so the "Welcome, ..." greeting elsewhere updates without
        // needing to log out and back in.
        $_SESSION['full_name'] = $full_name;
        $success = 'profile';
        $user['full_name'] = $full_name;
        $user['phone'] = $phone;
        $user['address'] = $address;
    }
}

// --- Form 2: change password ---------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Requiring the CURRENT password before allowing a change matters
    // for a specific reason: if someone walked away from an already
    // logged-in browser session, a stranger could otherwise lock the
    // real account owner out simply by setting a new password without
    // knowing the old one.
    if (!password_verify($current, $user['password'])) {
        $errors[] = "Current password is incorrect.";
    } elseif (strlen($new) < 8) {
        $errors[] = "New password must be at least 8 characters.";
    } elseif ($new !== $confirm) {
        $errors[] = "New passwords do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashed, $_SESSION['user_id']]);
        $success = 'password';
    }
}

include '../includes/header.php';
?>

<h1>My Profile</h1>

<div class="form-container" style="margin:0 0 24px;max-width:100%;">
    <h2>Account Details</h2>
    <?php if ($success === 'profile'): ?><div class="success-box">Profile updated successfully.</div><?php endif; ?>
    <?php if (!empty($errors) && isset($_POST['update_profile'])): ?>
        <div class="error-box"><ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="profile.php">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

        <!-- Email is shown but disabled: we treat email as the
             permanent account identifier, so changing it here would
             need extra safeguards (e.g. re-verification) that are out
             of scope for this assessment. -->
        <label>Email</label>
        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>

        <label>Phone</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">

        <label>Address</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">

        <button type="submit" name="update_profile" class="btn">Save Changes</button>
    </form>
</div>

<div class="form-container" style="margin:0;max-width:100%;">
    <h2>Change Password</h2>
    <?php if ($success === 'password'): ?><div class="success-box">Password changed successfully.</div><?php endif; ?>
    <?php if (!empty($errors) && isset($_POST['change_password'])): ?>
        <div class="error-box"><ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="profile.php">
        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required minlength="8">

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required minlength="8">

        <button type="submit" name="change_password" class="btn secondary">Change Password</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
