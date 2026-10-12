<?php
/**
 * CUSTOMER REGISTRATION PAGE
 * ---------------------------------------------------------------
 * Everyone who registers through this public form becomes a
 * 'customer' — staff and admin accounts are never created here,
 * they are created later by an admin from the Manage Users page.
 * This keeps account creation for privileged roles out of reach
 * of the public internet.
 */
require_once '../config/db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // trim() removes accidental leading/trailing spaces (a very common
    // typo when copy-pasting an email address).
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $address   = trim($_POST['address']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    // --- Server-side validation ---------------------------------
    // We validate here on the server even though the form also has
    // "required" attributes in the HTML, because browser-side
    // validation can always be bypassed (e.g. by disabling JavaScript
    // or sending the request with a tool instead of a browser). The
    // server is the only validation we can actually trust.
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email address.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // Check the email isn't already registered before inserting,
        // since the database's UNIQUE constraint on email would only
        // throw a raw SQL error rather than a friendly message.
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "An account with this email already exists.";
        } else {
            // Hash the password before it ever touches the database.
            // PASSWORD_DEFAULT currently maps to bcrypt, which is a
            // "slow" hashing algorithm designed specifically to make
            // password cracking (brute-forcing) impractically slow,
            // even if the database were ever leaked.
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
            $stmt->execute([$full_name, $email, $hashed, $phone, $address]);

            // Send them to the login page rather than logging them in
            // automatically — a small extra step, but it confirms
            // their password works before they rely on it.
            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - MediQuick Pharmacy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Create an Account</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <!-- htmlspecialchars() escapes any HTML-looking characters
                             before printing, so a user-typed value can never be
                             interpreted as HTML/JavaScript (this stops stored
                             Cross-Site Scripting / XSS attacks). -->
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <label>Full Name</label>
            <!-- Re-display whatever the user already typed if the form
                 fails validation, so they don't have to retype everything. -->
            <input type="text" name="full_name" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>

            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">

            <label>Address</label>
            <input type="text" name="address" value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">

            <label>Password</label>
            <input type="password" name="password" id="password" required minlength="8">

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required minlength="8">

            <label style="font-weight:normal; display:flex; align-items:center; gap:6px; margin-top:8px;">
                <input type="checkbox" id="show_password" style="width:auto;" onclick="togglePasswordVisibility()">
                Show password
            </label>

            <button type="submit">Register</button>
        </form>

        <script>
        // Toggles BOTH password fields together, since the whole point
        // is letting the user confirm their two entries actually match.
        function togglePasswordVisibility() {
            const type = document.getElementById('show_password').checked ? 'text' : 'password';
            document.getElementById('password').type = type;
            document.getElementById('confirm_password').type = type;
        }
        </script>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
