<?php
/**
 * ADMIN: MANAGE STAFF & USERS
 * ---------------------------------------------------------------
 * Two admin-only powers on this page:
 *   1. Create a new staff account (this is the ONLY way a staff
 *      account can be created — there's no public "register as staff"
 *      form, on purpose, so pharmacy staff access stays controlled).
 *   2. Change any existing user's role (promote a customer to staff,
 *      demote staff back to customer, or hand out admin rights).
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('admin');
define('BASE_URL', '../');
$page_title = 'Manage Users';
$errors = [];
$success = false;

// --- Create a new staff account -------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($full_name)) $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'staff')");
            $stmt->execute([$full_name, $email, $hashed]);
            $success = true;
        }
    }
}

// --- Change an existing user's role ----------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'];

    // Safety check: don't let an admin accidentally change their OWN
    // role from this form (e.g. demoting themselves out of admin and
    // getting locked out of this very page). The role dropdown for
    // your own row is hidden in the HTML below anyway, but we check
    // again here server-side too, since the HTML alone can't be trusted.
    if ($user_id !== (int)$_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->execute([$role, $user_id]);
    }
    header("Location: manage_users.php");
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY role, full_name")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Manage Staff &amp; Users</h1>

<div class="card-box">
    <h2>All Accounts</h2>
    <table class="data-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Change Role</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo ucfirst($u['role']); ?></td>
                <td>
                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                        <form method="POST" action="manage_users.php" style="display:flex;gap:6px;">
                            <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                            <select name="role">
                                <?php foreach (['customer','staff','admin'] as $r): ?>
                                    <option value="<?php echo $r; ?>" <?php echo $u['role'] === $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="change_role" class="btn secondary">Save</button>
                        </form>
                    <?php else: ?>
                        <em>(you)</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="form-container" style="margin:0;max-width:100%;">
    <h2>Create Staff Account</h2>
    <?php if ($success): ?><div class="success-box">Staff account created.</div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="error-box"><ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul></div><?php endif; ?>

    <form method="POST" action="manage_users.php">
        <label>Full Name</label>
        <input type="text" name="full_name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Temporary Password</label>
        <input type="password" name="password" required minlength="8">
        <button type="submit" name="add_staff" class="btn">Create Staff Account</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
