<?php
/**
 * PRODUCT DETAIL PAGE
 * ---------------------------------------------------------------
 * Shows everything about one product: photo, description, dosage
 * guidelines, safety instructions, and an "Add to Cart" form. This
 * page does NOT require login — a visitor can browse and even add
 * items to their cart before creating an account; login is only
 * required once they reach Checkout.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
define('BASE_URL', '../');

// (int) cast protects against someone tampering with the URL to pass
// something that isn't a plain number, e.g. ?id=1;DROP TABLE products.
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// If no product matches that ID (bad link, deleted product, tampered
// URL), don't show a broken page — just send them back to the shop.
if (!$product) {
    header("Location: products.php");
    exit;
}

$added = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // max(1, ...) guards against a quantity of 0 or a negative number
    // being submitted (e.g. by editing the HTML in browser dev tools).
    $qty = max(1, (int)$_POST['quantity']);
    cart_add($product_id, $qty);
    $added = true;
}

$page_title = $product['product_name'];
include '../includes/header.php';
?>

<a href="products.php">&larr; Back to Shop</a>

<div class="card-box" style="margin-top:16px;">
    <?php if (!empty($product['image_path'])): ?>
        <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="product-detail-image">
    <?php endif; ?>

    <span class="<?php echo $product['requires_prescription'] ? 'rx-badge' : 'otc-badge'; ?>">
        <?php echo $product['requires_prescription'] ? 'Prescription Required' : 'Over-the-Counter'; ?>
    </span>
    <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
    <p class="price">Rs. <?php echo number_format($product['price'], 2); ?></p>
    <p><?php echo $product['stock_qty'] > 0 ? htmlspecialchars($product['stock_qty']) . ' in stock' : 'Out of stock'; ?></p>

    <h3>Description</h3>
    <!-- nl2br() turns line breaks the staff typed in the textarea into
         actual <br> tags, so multi-line descriptions display correctly. -->
    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

    <h3>Dosage Guidelines</h3>
    <p><?php echo nl2br(htmlspecialchars($product['dosage_info'])); ?></p>

    <h3>Safety Instructions</h3>
    <p><?php echo nl2br(htmlspecialchars($product['safety_info'])); ?></p>

    <?php if ($product['requires_prescription']): ?>
        <div class="error-box">This item requires a valid prescription. Upload your prescription before checkout — our staff will verify it.</div>
    <?php endif; ?>

    <?php if ($added): ?>
        <div class="success-box">Added to cart. <a href="cart.php">View cart</a></div>
    <?php endif; ?>

    <?php if ($product['stock_qty'] > 0): ?>
        <form method="POST" action="product_detail.php?id=<?php echo $product_id; ?>">
            <label>Quantity</label>
            <!-- max attribute stops the browser's own +/- controls from
                 exceeding available stock; the server should still double-check
                 this at checkout time, since this is only a front-end hint. -->
            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_qty']; ?>" style="width:100px;">
            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
        </form>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
