<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_role('customer');
define('BASE_URL', '../');
$page_title = 'Order Confirmed';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: dashboard.php");
    exit;
}

include '../includes/header.php';
?>

<div class="card-box" style="text-align:center;">
    <h1>Thank you! Your order has been placed.</h1>
    <p>Order #<?php echo $order['order_id']; ?> — Total: Rs. <?php echo number_format($order['total_amount'], 2); ?></p>
    <p>Status: <span class="status-pill status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
    <a href="dashboard.php" class="btn">View My Orders</a>
</div>

<?php include '../includes/footer.php'; ?>
