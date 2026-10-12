<?php
/**
 * STAFF DASHBOARD
 * ---------------------------------------------------------------
 * A quick-glance summary for pharmacy staff when they log in: how
 * many things currently need their attention, plus shortcuts to each
 * staff tool. Each number is its own small COUNT(*) query rather than
 * one big query, which keeps each one easy to read and change independently.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('staff'); // only accounts with role = 'staff' can reach this page
define('BASE_URL', '../');
$page_title = 'Staff Dashboard';

$pending_rx     = $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE status = 'pending'")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending' OR status = 'confirmed'")->fetchColumn();
$low_stock      = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_qty < 20")->fetchColumn();
$open_inquiries = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'open'")->fetchColumn();

include '../includes/header.php';
?>

<h1>Staff Dashboard</h1>

<div class="stat-grid">
    <div class="stat-box"><div class="num"><?php echo $pending_rx; ?></div><div class="label">Pending Prescriptions</div></div>
    <div class="stat-box"><div class="num"><?php echo $pending_orders; ?></div><div class="label">Active Orders</div></div>
    <div class="stat-box"><div class="num"><?php echo $low_stock; ?></div><div class="label">Low Stock Products</div></div>
    <div class="stat-box"><div class="num"><?php echo $open_inquiries; ?></div><div class="label">Open Inquiries</div></div>
</div>

<div class="card-box">
    <h2>Quick Links</h2>
    <p><a href="prescriptions.php" class="btn">Verify Prescriptions</a></p>
    <p><a href="inventory.php" class="btn">Manage Inventory</a></p>
    <p><a href="orders.php" class="btn">View Orders</a></p>
    <p><a href="inquiries.php" class="btn">Respond to Inquiries</a></p>
</div>

<?php include '../includes/footer.php'; ?>
