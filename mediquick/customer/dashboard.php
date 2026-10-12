<?php
/**
 * CUSTOMER DASHBOARD ("My Orders")
 * ---------------------------------------------------------------
 * Simple read-only history page: every order this customer has ever
 * placed, newest first, with its current status and payment status.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('customer');
define('BASE_URL', '../');
$page_title = 'My Orders';

// WHERE user_id = ? is what keeps this private — a customer only ever
// sees their OWN orders, never anyone else's, no matter what URL they try.
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>

<h2>Order History</h2>
<?php if (empty($orders)): ?>
    <p>You haven't placed any orders yet. <a href="products.php">Start shopping</a></p>
<?php else: ?>
    <table class="data-table">
        <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th>Payment</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?php echo $o['order_id']; ?></td>
                <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                <td>Rs. <?php echo number_format($o['total_amount'], 2); ?></td>
                <td><span class="status-pill status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                <td><span class="status-pill status-<?php echo $o['payment_status']; ?>"><?php echo ucfirst($o['payment_status']); ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
