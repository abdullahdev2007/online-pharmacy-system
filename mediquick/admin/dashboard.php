<?php
/**
 * ADMIN DASHBOARD
 * ---------------------------------------------------------------
 * A birds-eye view of the whole system for admins: how many
 * customers/staff exist, how many orders have ever been placed, and
 * total revenue collected so far.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('admin');
define('BASE_URL', '../');
$page_title = 'Admin Dashboard';

$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_staff      = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn();
$total_orders     = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Only sum orders that actually show payment_status = 'paid' — this
// avoids counting revenue from an order that was never completed.
$total_revenue    = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'")->fetchColumn();

include '../includes/header.php';
?>

<h1>Admin Dashboard</h1>

<div class="stat-grid">
    <div class="stat-box"><div class="num"><?php echo $total_customers; ?></div><div class="label">Customers</div></div>
    <div class="stat-box"><div class="num"><?php echo $total_staff; ?></div><div class="label">Staff Members</div></div>
    <div class="stat-box"><div class="num"><?php echo $total_orders; ?></div><div class="label">Total Orders</div></div>
    <!-- ?? 0 fallback: SUM() returns NULL (not 0) if there are no
         matching rows yet, e.g. on a freshly-installed system with
         zero paid orders — this avoids printing "Rs. " with nothing after it. -->
    <div class="stat-box"><div class="num">Rs. <?php echo number_format($total_revenue ?? 0, 2); ?></div><div class="label">Revenue</div></div>
</div>

<div class="card-box">
    <h2>Quick Links</h2>
    <p><a href="manage_users.php" class="btn">Manage Staff &amp; Users</a></p>
</div>

<?php include '../includes/footer.php'; ?>
