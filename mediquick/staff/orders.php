<?php
/**
 * STAFF: MANAGE ORDERS
 * ---------------------------------------------------------------
 * Lets staff move an order through its fulfilment lifecycle:
 * pending -> confirmed -> shipped -> delivered (or cancelled at any
 * point). This is separate from payment_status, which is set
 * automatically at checkout — this page only ever touches the
 * fulfilment status, not payment.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('staff');
define('BASE_URL', '../');
$page_title = 'Orders';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->execute([$status, $order_id]);
    header("Location: orders.php");
    exit;
}

// JOIN users so we can show the customer's name/email alongside each
// order without a separate lookup query per row.
$orders = $pdo->query("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>All Orders</h1>

<table class="data-table">
    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Payment</th><th>Update</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td>#<?php echo $o['order_id']; ?></td>
            <td><?php echo htmlspecialchars($o['full_name']); ?><br><small><?php echo htmlspecialchars($o['email']); ?></small></td>
            <td>Rs. <?php echo number_format($o['total_amount'], 2); ?></td>
            <td><span class="status-pill status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
            <td><span class="status-pill status-<?php echo $o['payment_status']; ?>"><?php echo ucfirst($o['payment_status']); ?></span></td>
            <td>
                <form method="POST" action="orders.php" style="display:flex;gap:6px;">
                    <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                    <select name="status">
                        <?php foreach (['pending','confirmed','shipped','delivered','cancelled'] as $s): ?>
                            <!-- Pre-select whatever the order's current status is,
                                 so the dropdown reflects reality when the page loads. -->
                            <option value="<?php echo $s; ?>" <?php echo $o['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn secondary">Save</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
