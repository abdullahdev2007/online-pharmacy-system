<?php
/**
 * LOGIN PAGE
 * ---------------------------------------------------------------
 * Handles both showing the login form (GET request) and processing
 * it (POST request). After a successful login we redirect the user
 * to a different dashboard depending on their role, since customers,
 * staff and admins each have a completely different "home page".
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password']; // the PLAIN password the user just typed

    // Look up the account by email. We select the whole row (not just
    // the password) because if login succeeds we need full_name and
    // role too, to store in the session.
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // password_verify() compares the plain password the user typed
    // against the bcrypt HASH stored in the database — we never store
    // or compare plain passwords directly. If $user is false (no such
    // email) PHP short-circuits before even calling password_verify,
    // so a non-existent email can't cause a warning here.
    if ($user && password_verify($password, $user['password'])) {

        // Save just enough information in the session to identify
        // this user on every later page load, without having to
        // query the database again on every single page.
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];

        // Send the user to the dashboard that matches their role.
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] === 'staff') {
            header("Location: ../staff/dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit; // always exit immediately after a header() redirect
    } else {
        // Deliberately vague error message — we say "Invalid email or
        // password" rather than "that email doesn't exist" or "wrong
        // password", so an attacker trying random emails can't use
        // our error messages to figure out which emails are registered.
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - MediQuick Pharmacy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>

        <?php if (isset($_GET['registered'])): ?>
            <div class="success-box">Account created successfully. Please log in.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" id="password" required>

            <!-- Show password checkbox: purely a front-end convenience.
                 Clicking it just switches the input's "type" attribute
                 between "password" (dots) and "text" (plain letters)
                 using a couple of lines of JavaScript below. -->
            <label style="font-weight:normal; display:flex; align-items:center; gap:6px; margin-top:8px;">
                <input type="checkbox" id="show_password" style="width:auto;" onclick="togglePasswordVisibility()">
                Show password
            </label>

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    <script>
        // Toggles the password field between hidden (dots) and visible
        // (plain text) so users can double-check what they typed.
        function togglePasswordVisibility() {
            const type = document.getElementById('show_password').checked ? 'text' : 'password';
            document.getElementById('password').type = type;
        }
    </script>
</body>
</html>
