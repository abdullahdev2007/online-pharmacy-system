<?php
/**
 * SHOPPING CART PAGE
 * ---------------------------------------------------------------
 * Reads the product IDs stored in $_SESSION['cart'], looks up their
 * current details from the database (never trusting a price that
 * might have been stored in the session earlier), and lets the
 * customer adjust quantities or remove items before checking out.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
define('BASE_URL', '../');
$page_title = 'Your Cart';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// This page has two different "Update Cart" and "Remove" buttons in
// the same <form> (see the HTML below) — we tell them apart by which
// button name was actually submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // $_POST['qty'] arrives as an array like [5 => "2", 9 => "1"]
        // because each quantity input is named qty[<product_id>].
        foreach ($_POST['qty'] as $pid => $qty) {
            cart_update((int)$pid, (int)$qty);
        }
    } elseif (isset($_POST['remove'])) {
        cart_remove((int)$_POST['remove']);
    }
}

$cart_items = [];
$total = 0;
$requires_prescription = false;

if (!empty($_SESSION['cart'])) {
    // Fetch all cart products in a single query rather than one query
    // per item — much faster, and avoids the "N+1 query" problem.
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['product_id']];
        $subtotal = $qty * $p['price']; // always calculated from the CURRENT database price
        $total += $subtotal;
        if ($p['requires_prescription']) $requires_prescription = true;
        $cart_items[] = ['product' => $p, 'qty' => $qty, 'subtotal' => $subtotal];
    }
}

include '../includes/header.php';
?>

<h1>Your Cart</h1>

<?php if (empty($cart_items)): ?>
    <p>Your cart is empty. <a href="products.php">Browse products</a></p>
<?php else: ?>
    <form method="POST" action="cart.php">
        <table class="data-table">
            <thead>
                <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($item['product']['product_name']); ?>
                            <?php if ($item['product']['requires_prescription']): ?>
                                <span class="rx-badge">Rx</span>
                            <?php endif; ?>
                        </td>
                        <td>Rs. <?php echo number_format($item['product']['price'], 2); ?></td>
                        <td><input type="number" name="qty[<?php echo $item['product']['product_id']; ?>]" value="<?php echo $item['qty']; ?>" min="1" max="<?php echo $item['product']['stock_qty']; ?>" style="width:70px;"></td>
                        <td>Rs. <?php echo number_format($item['subtotal'], 2); ?></td>
                        <td>
                            <!-- The product_id travels along as this button's VALUE,
                                 so the form handler above knows which row to remove. -->
                            <button type="submit" name="remove" value="<?php echo $item['product']['product_id']; ?>" class="btn danger">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-top:16px;"><button type="submit" name="update" class="btn secondary">Update Cart</button></p>
    </form>

    <?php if ($requires_prescription): ?>
        <div class="error-box">Your cart contains a prescription item. You'll need to upload a prescription during checkout.</div>
    <?php endif; ?>

    <div class="card-box" style="margin-top:20px;">
        <h2>Total: Rs. <?php echo number_format($total, 2); ?></h2>
        <a href="checkout.php" class="btn">Proceed to Checkout</a>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
