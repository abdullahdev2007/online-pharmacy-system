<?php
/**
 * CHECKOUT PAGE
 * ---------------------------------------------------------------
 * This is the most important page in the whole system from a safety
 * point of view. Two things must both be true before an order can be
 * placed:
 *   1. Every product is still in stock in the quantity requested.
 *   2. If the cart contains ANY prescription-only product, the
 *      customer must have at least one prescription that staff have
 *      already reviewed and marked "approved".
 * If either check fails, checkout is blocked and the customer is
 * told exactly what to do next.
 *
 * PAYMENT SECURITY NOTE (see report for full discussion):
 * This project simulates payment locally rather than integrating a
 * live gateway, since a real merchant account is out of scope for an
 * academic environment. In production, this is the point where a Sri
 * Lankan PCI-DSS certified gateway such as PayHere would be
 * integrated: the customer's card details would be entered on
 * PayHere's own hosted/secure checkout page (never touching this
 * server), PayHere would notify this application via a signed
 * callback URL, and only then would $order['payment_status'] be set
 * to 'paid'. This means our own server never stores or transmits raw
 * card data.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_role('customer'); // only a logged-in customer can reach checkout
define('BASE_URL', '../');
$page_title = 'Checkout';
$errors = [];

// Nothing to check out if the cart is empty — send them back.
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Re-fetch every product fresh from the database (never trust
// anything about price or stock that might be cached in the session).
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
$requires_prescription = false;
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['product_id']];
    $total += $qty * $p['price'];
    if ($p['requires_prescription']) $requires_prescription = true;

    // Stock check: someone else may have bought the last units between
    // the time this customer added the item and now, so we re-check
    // here rather than trusting the number shown on the product page.
    if ($qty > $p['stock_qty']) {
        $errors[] = htmlspecialchars($p['product_name']) . " only has " . $p['stock_qty'] . " left in stock.";
    }
}

// If the cart needs a prescription, load this customer's APPROVED
// prescriptions so they can pick which one applies to this order.
$approved_prescriptions = [];
if ($requires_prescription) {
    $stmt = $pdo->prepare("SELECT * FROM prescriptions WHERE user_id = ? AND status = 'approved' ORDER BY uploaded_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $approved_prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($approved_prescriptions)) {
        // This is the safety-critical line: no approved prescription
        // on file means checkout simply cannot proceed, no matter what.
        $errors[] = "Your cart contains a prescription medicine. Please upload a prescription and wait for staff approval before checking out.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $shipping_address = trim($_POST['shipping_address']);
    $prescription_id = $requires_prescription ? (int)$_POST['prescription_id'] : null;

    if (empty($shipping_address)) {
        $errors[] = "Shipping address is required.";
    }

    if (empty($errors)) {
        // A single order actually involves FOUR separate writes to the
        // database: 1 order row, one order_items row per product, and
        // a stock decrease per product. If the server crashed halfway
        // through (e.g. after creating the order but before updating
        // stock), we would end up with an order that doesn't match
        // reality. A database TRANSACTION prevents this: nothing is
        // permanently saved until commit() runs; if anything fails,
        // rollBack() undoes everything that happened since
        // beginTransaction(), leaving the database exactly as it was.
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, prescription_id, total_amount, status, payment_status, shipping_address) VALUES (?, ?, ?, 'confirmed', 'paid', ?)");
            $stmt->execute([$_SESSION['user_id'], $prescription_id, $total, $shipping_address]);
            $order_id = $pdo->lastInsertId(); // the new order's auto-increment ID

            foreach ($products as $p) {
                $qty = $_SESSION['cart'][$p['product_id']];

                // Record what was actually bought at what price, so
                // that if the product's price changes later, this
                // historical order still shows the price paid at the time.
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $p['product_id'], $qty, $p['price']]);

                // Reduce stock by exactly what was ordered.
                $stmt = $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE product_id = ?");
                $stmt->execute([$qty, $p['product_id']]);
            }

            $pdo->commit(); // everything above succeeded — make it permanent
            cart_clear();
            header("Location: order_confirmation.php?order_id=$order_id");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack(); // something failed — undo all four+ writes above
            $errors[] = "Something went wrong placing your order. Please try again.";
        }
    }
}

// Pre-fill the shipping address box with whatever's already on file,
// so a returning customer doesn't have to retype it every time.
$stmt = $pdo->prepare("SELECT address FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$default_address = $stmt->fetchColumn();

include '../includes/header.php';
?>

<h1>Checkout</h1>

<?php if (!empty($errors)): ?>
    <div class="error-box">
        <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
    </div>
    <?php if ($requires_prescription && empty($approved_prescriptions)): ?>
        <a href="upload_prescription.php" class="btn">Upload Prescription</a>
    <?php endif; ?>
<?php else: ?>

<div class="card-box">
    <h2>Order Summary</h2>
    <table class="data-table">
        <thead><tr><th>Product</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): $qty = $_SESSION['cart'][$p['product_id']]; ?>
            <tr>
                <td><?php echo htmlspecialchars($p['product_name']); ?></td>
                <td><?php echo $qty; ?></td>
                <td>Rs. <?php echo number_format($qty * $p['price'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h3>Total: Rs. <?php echo number_format($total, 2); ?></h3>
</div>

<div class="form-container" style="margin:0 0 20px;max-width:100%;">
    <h2>Shipping &amp; Payment</h2>
    <form method="POST" action="checkout.php">
        <label>Shipping Address</label>
        <textarea name="shipping_address" rows="3" required><?php echo htmlspecialchars($default_address ?? ''); ?></textarea>

        <?php if ($requires_prescription): ?>
            <label>Select Approved Prescription</label>
            <select name="prescription_id" required>
                <?php foreach ($approved_prescriptions as $rx): ?>
                    <option value="<?php echo $rx['prescription_id']; ?>">
                        Prescription #<?php echo $rx['prescription_id']; ?> (approved <?php echo date('d M Y', strtotime($rx['reviewed_at'])); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <label>Payment Method</label>
        <select name="payment_method" required>
            <option value="card">Card Payment (simulated)</option>
            <option value="cod">Cash on Delivery</option>
        </select>
        <p style="font-size:13px;color:#666;">This is a simulated payment for demonstration purposes — no real transaction is processed.</p>

        <button type="submit" class="btn">Place Order</button>
    </form>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
